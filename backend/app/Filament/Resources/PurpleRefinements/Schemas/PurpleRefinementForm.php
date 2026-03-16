<?php

namespace App\Filament\Resources\PurpleRefinements\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Schema;

class PurpleRefinementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('紫洗练信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('refinement_id')
                            ->label('紫炼化ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('紫炼化名称')
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
