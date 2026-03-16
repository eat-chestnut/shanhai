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
                            ->label('monster_id')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('name')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('combat_role')
                            ->label('combat_role')
                            ->placeholder('boss / caster / vanguard')
                            ->maxLength(100),
                        TextInput::make('base_hp')
                            ->label('base_hp')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('base_atk')
                            ->label('base_atk')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Toggle::make('is_boss')
                            ->label('is_boss')
                            ->required()
                            ->default(false)
                            ->inline(false),
                        TextInput::make('behavior_profile.move_speed')
                            ->label('behavior_profile.move_speed')
                            ->numeric()
                            ->default(118),
                        TextInput::make('behavior_profile.attack_range')
                            ->label('behavior_profile.attack_range')
                            ->numeric()
                            ->default(68),
                        TextInput::make('behavior_profile.attack_interval')
                            ->label('behavior_profile.attack_interval')
                            ->numeric()
                            ->default(1.7),
                        TextInput::make('behavior_profile.aggro_range')
                            ->label('behavior_profile.aggro_range')
                            ->numeric()
                            ->default(190),
                        TextInput::make('behavior_profile.cooldown')
                            ->label('behavior_profile.cooldown')
                            ->numeric()
                            ->visible(fn (Get $get): bool => (bool) $get('is_boss')),
                        TextInput::make('behavior_profile.burst_ratio')
                            ->label('behavior_profile.burst_ratio')
                            ->numeric()
                            ->visible(fn (Get $get): bool => (bool) $get('is_boss')),
                        Repeater::make('behavior_profile.patterns')
                            ->label('behavior_profile.patterns')
                            ->defaultItems(0)
                            ->reorderable()
                            ->visible(fn (Get $get): bool => (bool) $get('is_boss'))
                            ->schema([
                                TextInput::make('skill_name')
                                    ->label('skill_name')
                                    ->required(),
                                TextInput::make('pattern_type')
                                    ->label('pattern_type')
                                    ->required()
                                    ->placeholder('area_burst / line_strike / summon'),
                                TextInput::make('cooldown')
                                    ->label('cooldown')
                                    ->numeric()
                                    ->default(6),
                                TextInput::make('burst_ratio')
                                    ->label('burst_ratio')
                                    ->numeric()
                                    ->default(0.25),
                                TextInput::make('phase_threshold')
                                    ->label('phase_threshold')
                                    ->numeric()
                                    ->default(1.0),
                                TextInput::make('telegraph')
                                    ->label('telegraph')
                                    ->columnSpanFull(),
                                TextInput::make('summon_monster_id')
                                    ->label('summon_monster_id'),
                                TextInput::make('summon_count')
                                    ->label('summon_count')
                                    ->numeric()
                                    ->default(1),
                                TextInput::make('radius')
                                    ->label('radius')
                                    ->numeric(),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
