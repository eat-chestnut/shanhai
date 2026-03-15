<?php

namespace App\Filament\Resources\CharacterClasses\Schemas;

use App\Enums\RoleType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class CharacterClassForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('职业信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('class_id')
                            ->label('class_id')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('class_name')
                            ->label('class_name')
                            ->required()
                            ->maxLength(100),
                        Select::make('role_type')
                            ->label('role_type')
                            ->required()
                            ->options(RoleType::options())
                            ->native(false)
                            ->rules([Rule::in(RoleType::values())]),
                        Toggle::make('is_open')
                            ->label('is_open')
                            ->required()
                            ->default(false)
                            ->inline(false),
                        Textarea::make('class_desc')
                            ->label('class_desc')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
