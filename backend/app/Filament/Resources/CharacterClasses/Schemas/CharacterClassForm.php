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
                            ->label('职业ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('class_name')
                            ->label('职业名称')
                            ->required()
                            ->maxLength(100),
                        Select::make('role_type')
                            ->label('角色类型')
                            ->required()
                            ->options(RoleType::options())
                            ->native(false)
                            ->rules([Rule::in(RoleType::values())]),
                        Toggle::make('is_open')
                            ->label('是否开启')
                            ->required()
                            ->default(false)
                            ->inline(false),
                        Textarea::make('class_desc')
                            ->label('职业描述')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
