<?php

namespace App\Filament\Resources\ScriptureWorldTiers\Schemas;

use App\Models\Scripture;
use App\Models\ScriptureDropTag;
use App\Models\ScriptureMonster;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ScriptureWorldTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('区间信息')
                    ->columns(2)
                    ->schema([
                        Select::make('scripture_id')
                            ->label('经卷')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(Scripture::getEnabledScriptureOptions()),
                        TextInput::make('world_level_start')
                            ->label('世界等级起点')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('world_level_end')
                            ->label('世界等级终点')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('new_feature_note')
                            ->label('阶段说明')
                            ->maxLength(255)
                            ->helperText('严格按正式 JSON 中的 new_feature_note 填写。'),
                    ]),
                Section::make('数值倍率')
                    ->columns(3)
                    ->schema([
                        TextInput::make('hp_scale')->label('生命倍率')->required()->numeric()->default(1),
                        TextInput::make('atk_scale')->label('攻击倍率')->required()->numeric()->default(1),
                        TextInput::make('def_scale')->label('防御倍率')->required()->numeric()->default(1),
                        TextInput::make('reward_scale')->label('掉落倍率')->required()->numeric()->default(1),
                        TextInput::make('gold_scale')->label('金币倍率')->required()->numeric()->default(1),
                    ]),
                Section::make('怪物池与掉落标签')
                    ->columns(1)
                    ->schema([
                        Select::make('normal_monster_ids')
                            ->label('普通怪池')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(ScriptureMonster::getEnabledMonsterOptions())
                            ->helperText('严格按 JSON 的 normal_monster_ids 配置。'),
                        Select::make('elite_monster_ids')
                            ->label('精英怪池')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(ScriptureMonster::getEnabledMonsterOptions()),
                        Select::make('boss_monster_ids')
                            ->label('Boss池')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(ScriptureMonster::getEnabledMonsterOptions()),
                        Select::make('extra_drop_tags')
                            ->label('额外掉落标签')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(ScriptureDropTag::getDropTagOptions())
                            ->helperText('保持 drop tag 原始结构，不做简化。'),
                    ]),
            ]);
    }
}
