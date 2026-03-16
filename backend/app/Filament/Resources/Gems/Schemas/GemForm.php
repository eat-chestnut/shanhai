<?php

namespace App\Filament\Resources\Gems\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as FormSection;
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
                            ->label('宝石ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('宝石名称')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('type')
                            ->label('宝石类型')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('attribute'),
                        TextInput::make('bonus_atk')
                            ->label('攻击加成')
                            ->numeric()
                            ->default(0),
                        TextInput::make('bonus_boss_dmg')
                            ->label('Boss伤害加成')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
