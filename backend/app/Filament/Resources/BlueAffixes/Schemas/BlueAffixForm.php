<?php

namespace App\Filament\Resources\BlueAffixes\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BlueAffixForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('蓝词条信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('affix_id')
                            ->label('affix_id')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('name')
                            ->required()
                            ->maxLength(100),
                        KeyValue::make('bonuses')
                            ->label('bonuses')
                            ->keyLabel('属性键')
                            ->valueLabel('属性值')
                            ->default([])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
