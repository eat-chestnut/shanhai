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
use Filament\Schemas\Components\Utilities\Get;
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
                            ->label('所属职业')
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
                            ->label('技能ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('skill_name')
                            ->label('技能名称')
                            ->required()
                            ->maxLength(100),
                        Select::make('type')
                            ->label('技能类型')
                            ->required()
                            ->native(false)
                            ->options(SkillType::options()),
                        Select::make('effect_type')
                            ->label('效果类型')
                            ->native(false)
                            ->options(SkillEffectType::options())
                            ->live(),
                        Select::make('target_type')
                            ->label('目标类型')
                            ->native(false)
                            ->options(SkillTargetType::options()),
                        Textarea::make('skill_desc')
                            ->label('技能描述')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('is_open')
                            ->label('是否开放')
                            ->default(true),
                    ]),
                FormSection::make('数值配置')
                    ->columns(3)
                    ->schema([
                        TextInput::make('cooldown')
                            ->label('冷却时间')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('cost')
                            ->label('消耗')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('unlock_level')
                            ->label('解锁等级')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('max_level')
                            ->label('最高等级')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(5),
                        TextInput::make('power_base')
                            ->label('基础威力')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('power_per_level')
                            ->label('每级成长')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('duration')
                            ->label('持续时间')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('chance')
                            ->label('触发概率')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step('0.0001')
                            ->default(0),
                        TextInput::make('stat_bonuses.bonus_attack_speed')
                            ->label('攻击速度加成')
                            ->numeric()
                            ->visible(fn (Get $get): bool => (string) $get('type') === 'passive'),
                        TextInput::make('stat_bonuses.bonus_damage_ratio')
                            ->label('伤害倍率加成')
                            ->numeric()
                            ->visible(fn (Get $get): bool => (string) $get('type') === 'passive'),
                        TextInput::make('effect_payload.target_count')
                            ->label('目标数量')
                            ->numeric()
                            ->visible(fn (Get $get): bool => in_array((string) $get('target_type'), ['multi', 'area'], true)),
                        TextInput::make('effect_payload.preferred_target')
                            ->label('优先目标规则')
                            ->placeholder('nearest / farthest_cluster / boss_or_high_threat'),
                        TextInput::make('effect_payload.telegraph_type')
                            ->label('预警区域类型')
                            ->placeholder('area / line'),
                        TextInput::make('effect_payload.status_name')
                            ->label('状态名称')
                            ->visible(fn (Get $get): bool => in_array((string) $get('effect_type'), ['control', 'dot', 'hot', 'damage'], true)),
                        TextInput::make('effect_payload.status_type')
                            ->label('状态类型')
                            ->visible(fn (Get $get): bool => in_array((string) $get('effect_type'), ['control', 'dot', 'hot', 'damage'], true)),
                        TextInput::make('effect_payload.status_duration')
                            ->label('状态持续时间')
                            ->numeric()
                            ->visible(fn (Get $get): bool => in_array((string) $get('effect_type'), ['control', 'dot', 'hot', 'damage'], true)),
                        TextInput::make('effect_payload.stack_rule')
                            ->label('叠层规则')
                            ->placeholder('refresh / stack / replace'),
                        TextInput::make('effect_payload.max_stacks')
                            ->label('最大层数')
                            ->numeric(),
                    ]),
                FormSection::make('加成与扩展')
                    ->columns(1)
                    ->schema([
                        KeyValue::make('stat_bonuses')
                            ->label('属性加成')
                            ->keyLabel('加成键')
                            ->valueLabel('加成值')
                            ->default([]),
                        KeyValue::make('effect_payload')
                            ->label('效果扩展参数')
                            ->keyLabel('扩展键')
                            ->valueLabel('扩展值')
                            ->default([])
                            ->helperText('用于补充 trigger、preferred_target、stack_rule 等轻量键值。'),
                    ]),
            ]);
    }
}
