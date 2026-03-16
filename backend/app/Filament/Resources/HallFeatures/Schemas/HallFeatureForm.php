<?php

namespace App\Filament\Resources\HallFeatures\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Schema;

class HallFeatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('基础信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('feature_id')
                            ->label('feature_id')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('feature_name')
                            ->label('功能名称')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('feature_type')
                            ->label('功能类型')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('daily_task'),
                    ]),
                FormSection::make('解锁与跳转')
                    ->columns(2)
                    ->schema([
                        TextInput::make('unlock_condition.level')
                            ->label('等级解锁')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('jump_target.page')
                            ->label('跳转页面')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('trial'),
                        KeyValue::make('unlock_condition.conditions')
                            ->label('条件解锁')
                            ->keyLabel('条件键')
                            ->valueLabel('条件值')
                            ->default([])
                            ->columnSpanFull(),
                        KeyValue::make('jump_target.params')
                            ->label('跳转参数')
                            ->keyLabel('参数键')
                            ->valueLabel('参数值')
                            ->default([])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
