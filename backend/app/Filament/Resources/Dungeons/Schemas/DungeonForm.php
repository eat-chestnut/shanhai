<?php

namespace App\Filament\Resources\Dungeons\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Schema;

class DungeonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('副本信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('dungeon_id')
                            ->label('dungeon_id')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('dungeon_name')
                            ->label('dungeon_name')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('unlock_level')
                            ->label('unlock_level')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                    ]),
            ]);
    }
}
