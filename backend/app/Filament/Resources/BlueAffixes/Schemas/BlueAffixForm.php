<?php

namespace App\Filament\Resources\BlueAffixes\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as FormSection;
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
                            ->label('蓝词条ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('蓝词条名称')
                            ->required()
                            ->maxLength(100),
                        KeyValue::make('bonuses')
                            ->label('属性加成')
                            ->keyLabel('属性键')
                            ->valueLabel('属性值')
                            ->default([])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
