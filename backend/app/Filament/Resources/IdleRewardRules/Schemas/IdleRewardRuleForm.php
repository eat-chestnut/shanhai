<?php

namespace App\Filament\Resources\IdleRewardRules\Schemas;

use App\Models\Item;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Schema;

class IdleRewardRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('基础规则')
                    ->columns(2)
                    ->schema([
                        TextInput::make('rule_id')
                            ->label('规则ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('rule_name')
                            ->label('规则名称')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('min_level')
                            ->label('最低等级')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('max_level')
                            ->label('最高等级')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(80),
                        TextInput::make('idle_cap_hours')
                            ->label('挂机上限小时')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(8),
                        TextInput::make('sort')
                            ->label('排序')
                            ->required()
                            ->numeric()
                            ->default(10),
                        Toggle::make('is_open')
                            ->label('是否开启')
                            ->default(true),
                        Textarea::make('bonus_hint')
                            ->label('收益提示')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                FormSection::make('收益速率')
                    ->schema([
                        Repeater::make('reward_rate')
                            ->label('收益速率')
                            ->defaultItems(1)
                            ->reorderable()
                            ->schema([
                                Select::make('item_id')
                                    ->label('物品')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->options(fn (): array => Item::getEnabledItemOptions()),
                                TextInput::make('count_per_hour')
                                    ->label('每小时产出')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(1),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->helperText('填写每小时产出，用于在线/离线统一挂机收益。'),
                    ]),
            ]);
    }
}
