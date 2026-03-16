<?php

namespace App\Filament\Resources\DungeonDifficulties\Schemas;

use App\Models\Dungeon;
use App\Models\DungeonDifficulty;
use App\Models\Monster;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section as FormSection;
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
                FormSection::make('副本难度信息')
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
                FormSection::make('刷怪规则配置')
                    ->columns(2)
                    ->schema([
                        Repeater::make('normal_monster_pool')
                            ->label('普通怪物池')
                            ->schema([
                                Select::make('monster_id')
                                    ->label('怪物ID')
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->options(fn (): array => Monster::query()
                                        ->orderBy('monster_id')
                                        ->get()
                                        ->mapWithKeys(static fn (Monster $monster): array => [
                                            $monster->monster_id => "{$monster->monster_id} / {$monster->monster_name}",
                                        ])
                                        ->all()),
                                TextInput::make('weight')
                                    ->label('权重')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->columnSpanFull(),
                        Repeater::make('elite_monster_pool')
                            ->label('精英怪物池')
                            ->schema([
                                Select::make('monster_id')
                                    ->label('怪物ID')
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->options(fn (): array => Monster::query()
                                        ->orderBy('monster_id')
                                        ->get()
                                        ->mapWithKeys(static fn (Monster $monster): array => [
                                            $monster->monster_id => "{$monster->monster_id} / {$monster->monster_name}",
                                        ])
                                        ->all()),
                                TextInput::make('weight')
                                    ->label('权重')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->columnSpanFull(),
                        Repeater::make('boss_monster_pool')
                            ->label('Boss怪物池')
                            ->schema([
                                Select::make('monster_id')
                                    ->label('怪物ID')
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->options(fn (): array => Monster::query()
                                        ->orderBy('monster_id')
                                        ->get()
                                        ->mapWithKeys(static fn (Monster $monster): array => [
                                            $monster->monster_id => "{$monster->monster_id} / {$monster->monster_name}",
                                        ])
                                        ->all()),
                                TextInput::make('weight')
                                    ->label('权重')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->columnSpanFull(),
                        TextInput::make('normal_spawn_interval')
                            ->label('普通怪刷新间隔(秒)')
                            ->numeric()
                            ->default(5)
                            ->minValue(1),
                        TextInput::make('normal_spawn_count')
                            ->label('单次刷新普通怪数量')
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                        TextInput::make('max_normal_on_screen')
                            ->label('同屏普通怪上限')
                            ->numeric()
                            ->default(5)
                            ->minValue(1),
                        TextInput::make('elite_trigger_kills')
                            ->label('击杀普通怪后出现精英怪数量')
                            ->numeric()
                            ->default(10)
                            ->minValue(1),
                        TextInput::make('boss_trigger_elite_kills')
                            ->label('击杀精英怪后出现Boss数量')
                            ->numeric()
                            ->default(3)
                            ->minValue(1),
                        Toggle::make('stop_spawning_after_boss')
                            ->label('Boss出现后停止刷新其他怪物')
                            ->default(true),
                        Toggle::make('clear_dungeon_after_boss')
                            ->label('Boss击杀后通关')
                            ->default(true),
                    ]),
            ]);
    }
}
