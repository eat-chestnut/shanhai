<?php

namespace App\Filament\Resources\Gems\Schemas;

use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('宝石信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('gem_id')
                            ->label('gem_id')
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
                            ->placeholder('attribute'),
                        TextInput::make('bonus_atk')
                            ->label('bonus_atk')
                            ->numeric()
                            ->default(0),
                        TextInput::make('bonus_boss_dmg')
                            ->label('bonus_boss_dmg')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
