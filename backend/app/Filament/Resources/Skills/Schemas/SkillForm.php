<?php

namespace App\Filament\Resources\Skills\Schemas;

use App\Enums\SkillEffectType;
use App\Enums\SkillTargetType;
use App\Enums\SkillType;
use App\Models\CharacterClass;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Schema;

class SkillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('基础信息')
                    ->columns(2)
                    ->schema([
                        Select::make('class_id')
                            ->label('class_id')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(fn (): array => CharacterClass::query()
                                ->orderBy('class_id')
                                ->get()
                                ->mapWithKeys(static fn (CharacterClass $class): array => [
                                    $class->class_id => "{$class->class_id} / {$class->class_name}",
                                ])
                                ->all()),
                        TextInput::make('skill_id')
                            ->label('skill_id')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('skill_name')
                            ->label('skill_name')
                            ->required()
                            ->maxLength(100),
                        Select::make('type')
                            ->label('type')
                            ->required()
                            ->native(false)
                            ->options(SkillType::options()),
                        Select::make('effect_type')
                            ->label('effect_type')
                            ->native(false)
                            ->options(SkillEffectType::options()),
                        Select::make('target_type')
                            ->label('target_type')
                            ->native(false)
                            ->options(SkillTargetType::options()),
                        Textarea::make('skill_desc')
                            ->label('skill_desc')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('is_open')
                            ->label('is_open')
                            ->default(true),
                    ]),
                FormSection::make('数值配置')
                    ->columns(3)
                    ->schema([
                        TextInput::make('cooldown')
                            ->label('cooldown')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('cost')
                            ->label('cost')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('unlock_level')
                            ->label('unlock_level')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('max_level')
                            ->label('max_level')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(5),
                        TextInput::make('power_base')
                            ->label('power_base')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('power_per_level')
                            ->label('power_per_level')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('duration')
                            ->label('duration')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('chance')
                            ->label('chance')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step('0.0001')
                            ->default(0),
                    ]),
                FormSection::make('加成与扩展')
                    ->columns(1)
                    ->schema([
                        KeyValue::make('stat_bonuses')
                            ->label('stat_bonuses')
                            ->keyLabel('加成键')
                            ->valueLabel('加成值')
                            ->default([]),
                        KeyValue::make('effect_payload')
                            ->label('effect_payload')
                            ->keyLabel('扩展键')
                            ->valueLabel('扩展值')
                            ->default([])
                            ->helperText('例如 trigger、target_count、status_name、status_type、status_power_ratio。'),
                    ]),
            ]);
    }
}
