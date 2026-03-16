<?php

namespace App\Filament\Resources\Equipment\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Schema;

class EquipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('装备信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('equip_id')
                            ->label('装备ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('装备名称')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('type')
                            ->label('装备类型')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('weapon'),
                        TextInput::make('level')
                            ->label('等级')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('base_atk')
                            ->label('基础攻击')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('base_def')
                            ->label('基础防御')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ]),
            ]);
    }
}
