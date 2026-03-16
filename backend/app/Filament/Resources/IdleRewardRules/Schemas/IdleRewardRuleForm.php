<?php

namespace App\Filament\Resources\IdleRewardRules\Schemas;

use Filament\Forms\Components\Repeater;
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
                            ->label('rule_id')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('rule_name')
                            ->label('rule_name')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('min_level')
                            ->label('min_level')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('max_level')
                            ->label('max_level')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(80),
                        TextInput::make('idle_cap_hours')
                            ->label('idle_cap_hours')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(8),
                        TextInput::make('sort')
                            ->label('sort')
                            ->required()
                            ->numeric()
                            ->default(10),
                        Toggle::make('is_open')
                            ->label('is_open')
                            ->default(true),
                        Textarea::make('bonus_hint')
                            ->label('bonus_hint')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                FormSection::make('收益速率')
                    ->schema([
                        Repeater::make('reward_rate')
                            ->label('reward_rate')
                            ->defaultItems(1)
                            ->reorderable()
                            ->schema([
                                TextInput::make('item_id')
                                    ->label('item_id')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('count_per_hour')
                                    ->label('count_per_hour')
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
