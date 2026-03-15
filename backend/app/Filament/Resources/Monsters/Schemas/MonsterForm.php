<?php

namespace App\Filament\Resources\Monsters\Schemas;

use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MonsterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('怪物信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('monster_id')
                            ->label('monster_id')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('name')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('base_hp')
                            ->label('base_hp')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('base_atk')
                            ->label('base_atk')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Toggle::make('is_boss')
                            ->label('is_boss')
                            ->required()
                            ->default(false)
                            ->inline(false),
                    ]),
            ]);
    }
}
