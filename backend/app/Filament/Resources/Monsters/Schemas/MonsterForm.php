<?php

namespace App\Filament\Resources\Monsters\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MonsterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('怪物信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('monster_id')
                            ->label('怪物ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('怪物名称')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('combat_role')
                            ->label('战斗角色')
                            ->placeholder('boss / caster / vanguard')
                            ->maxLength(100),
                        TextInput::make('base_hp')
                            ->label('基础生命值')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('base_atk')
                            ->label('基础攻击力')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Toggle::make('is_boss')
                            ->label('是否为Boss')
                            ->required()
                            ->default(false)
                            ->inline(false),
                        TextInput::make('behavior_profile.move_speed')
                            ->label('移动速度')
                            ->numeric()
                            ->default(118),
                        TextInput::make('behavior_profile.attack_range')
                            ->label('攻击范围')
                            ->numeric()
                            ->default(68),
                        TextInput::make('behavior_profile.attack_interval')
                            ->label('攻击间隔')
                            ->numeric()
                            ->default(1.7),
                        TextInput::make('behavior_profile.aggro_range')
                            ->label('仇恨范围')
                            ->numeric()
                            ->default(190),
                        TextInput::make('behavior_profile.cooldown')
                            ->label('冷却时间')
                            ->numeric()
                            ->visible(fn (Get $get): bool => (bool) $get('is_boss')),
                        TextInput::make('behavior_profile.burst_ratio')
                            ->label('爆发比例')
                            ->numeric()
                            ->visible(fn (Get $get): bool => (bool) $get('is_boss')),
                        Repeater::make('behavior_profile.patterns')
                            ->label('技能模式')
                            ->defaultItems(0)
                            ->reorderable()
                            ->visible(fn (Get $get): bool => (bool) $get('is_boss'))
                            ->schema([
                                TextInput::make('skill_name')
                                    ->label('技能名称')
                                    ->required(),
                                TextInput::make('pattern_type')
                                    ->label('模式类型')
                                    ->required()
                                    ->placeholder('area_burst / line_strike / summon'),
                                TextInput::make('cooldown')
                                    ->label('冷却时间')
                                    ->numeric()
                                    ->default(6),
                                TextInput::make('burst_ratio')
                                    ->label('爆发比例')
                                    ->numeric()
                                    ->default(0.25),
                                TextInput::make('phase_threshold')
                                    ->label('阶段阈值')
                                    ->numeric()
                                    ->default(1.0),
                                TextInput::make('telegraph')
                                    ->label('预警提示')
                                    ->columnSpanFull(),
                                TextInput::make('summon_monster_id')
                                    ->label('召唤怪物ID'),
                                TextInput::make('summon_count')
                                    ->label('召唤数量')
                                    ->numeric()
                                    ->default(1),
                                TextInput::make('radius')
                                    ->label('作用半径')
                                    ->numeric(),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
