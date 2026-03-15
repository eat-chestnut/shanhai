<?php

namespace App\Filament\Resources\MainlineChapters\Schemas;

use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MainlineChapterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('章节信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('chapter_id')
                            ->label('chapter_id')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('chapter_name')
                            ->label('chapter_name')
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
