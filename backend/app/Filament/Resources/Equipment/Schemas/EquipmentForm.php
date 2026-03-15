<?php

namespace App\Filament\Resources\Equipment\Schemas;

use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\TextInput;
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
                            ->label('equip_id')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('name')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('type')
                            ->label('type')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('weapon'),
                        TextInput::make('level')
                            ->label('level')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('base_atk')
                            ->label('base_atk')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('base_def')
                            ->label('base_def')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ]),
            ]);
    }
}
