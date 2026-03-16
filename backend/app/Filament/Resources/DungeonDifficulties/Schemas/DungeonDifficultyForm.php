<?php

namespace App\Filament\Resources\DungeonDifficulties\Schemas;

use App\Models\Dungeon;
use App\Models\DungeonDifficulty;
use App\Models\Monster;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class DungeonDifficultyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('副本难度信息')
                    ->columns(2)
                    ->schema([
                        Select::make('dungeon_id')
                            ->label('副本ID')
                            ->required()
                            ->live()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(fn (): array => Dungeon::query()
                                ->orderBy('dungeon_id')
                                ->get()
                                ->mapWithKeys(static fn (Dungeon $dungeon): array => [
                                    $dungeon->dungeon_id => "{$dungeon->dungeon_id} / {$dungeon->dungeon_name}",
                                ])
                                ->all()),
                        TextInput::make('difficulty_id')
                            ->label('难度ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(
                                table: DungeonDifficulty::class,
                                column: 'difficulty_id',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule->where('dungeon_id', $get('dungeon_id')),
                            ),
                        TextInput::make('recommended_power')
                            ->label('推荐战力')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('first_clear_reward_group_id')
                            ->label('首通奖励组ID')
                            ->maxLength(100)
                            ->placeholder('reward_dungeon_gem_easy')
                            ->columnSpanFull(),
                    ]),

                Section::make('普通怪配置')
                    ->description('配置普通怪物的刷新规则')
                    ->columns(2)
                    ->schema([
                        TagsInput::make('normal_monster_ids')
                            ->label('普通怪物ID列表')
                            ->required()
                            ->separator(',')
                            ->placeholder('monster_wolf, monster_snake')
                            ->helperText('输入怪物ID，用逗号分隔')
                            ->columnSpanFull(),
                        TextInput::make('normal_spawn_interval')
                            ->label('刷新间隔（秒）')
                            ->numeric()
                            ->default(3)
                            ->minValue(1)
                            ->helperText('普通怪每次刷新的时间间隔'),
                        TextInput::make('normal_spawn_count')
                            ->label('单次刷新数量')
                            ->numeric()
                            ->default(2)
                            ->minValue(1)
                            ->helperText('每次刷新的普通怪数量'),
                        TextInput::make('normal_alive_limit')
                            ->label('同时存在上限')
                            ->numeric()
                            ->default(6)
                            ->minValue(1)
                            ->helperText('场上同时存在的普通怪最大数量'),
                    ]),

                Section::make('精英怪配置')
                    ->description('配置精英怪物的刷新规则')
                    ->columns(2)
                    ->schema([
                        TagsInput::make('elite_monster_ids')
                            ->label('精英怪物ID列表')
                            ->required()
                            ->separator(',')
                            ->placeholder('monster_wolf_elite')
                            ->helperText('输入精英怪物ID，用逗号分隔')
                            ->columnSpanFull(),
                        TextInput::make('elite_spawn_interval')
                            ->label('刷新间隔（秒）')
                            ->numeric()
                            ->default(6)
                            ->minValue(1)
                            ->helperText('精英怪每次刷新的时间间隔'),
                        TextInput::make('elite_spawn_count')
                            ->label('单次刷新数量')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->helperText('每次刷新的精英怪数量'),
                        TextInput::make('elite_alive_limit')
                            ->label('同时存在上限')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->helperText('场上同时存在的精英怪最大数量'),
                    ]),

                Section::make('Boss配置')
                    ->description('配置Boss怪物')
                    ->columns(2)
                    ->schema([
                        Select::make('boss_monster_id')
                            ->label('Boss怪物ID')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('选择Boss怪物')
                            ->helperText('该难度的最终Boss')
                            ->options(fn (): array => Monster::query()
                                ->orderBy('monster_id')
                                ->get()
                                ->mapWithKeys(static fn (Monster $monster): array => [
                                    $monster->monster_id => "{$monster->monster_id} / {$monster->monster_name}",
                                ])
                                ->all()),
                    ]),

                Section::make('阶段触发条件')
                    ->description('配置从普通怪到精英怪再到Boss的触发条件')
                    ->columns(2)
                    ->schema([
                        TextInput::make('normal_kill_to_spawn_elite')
                            ->label('普通怪击杀数量触发精英怪')
                            ->numeric()
                            ->default(12)
                            ->minValue(1)
                            ->helperText('击杀这么多普通怪后开始出现精英怪'),
                        TextInput::make('elite_kill_to_spawn_boss')
                            ->label('精英怪击杀数量触发Boss')
                            ->numeric()
                            ->default(3)
                            ->minValue(1)
                            ->helperText('击杀这么多精英怪后出现Boss'),
                    ]),

                Section::make('流程控制')
                    ->description('控制副本流程的规则')
                    ->columns(1)
                    ->schema([
                        Toggle::make('stop_spawn_after_boss_appears')
                            ->label('Boss出现后停止刷怪')
                            ->default(true)
                            ->helperText('Boss出现后停止刷新普通怪和精英怪'),
                        Toggle::make('clear_on_boss_killed')
                            ->label('Boss击杀后通关')
                            ->default(true)
                            ->helperText('击杀Boss后立即通关副本'),
                    ]),
            ]);
    }
}
