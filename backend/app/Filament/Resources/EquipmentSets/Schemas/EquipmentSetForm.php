<?php

namespace App\Filament\Resources\EquipmentSets\Schemas;

use App\Models\Equipment;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EquipmentSetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('套装信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('set_id')
                            ->label('set_id')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('level')
                            ->label('level')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        TagsInput::make('pieces')
                            ->label('pieces')
                            ->required()
                            ->reorderable()
                            ->suggestions(fn (): array => Equipment::query()->orderBy('equip_id')->pluck('equip_id')->all())
                            ->columnSpanFull(),
                        Repeater::make('effects')
                            ->label('effects')
                            ->defaultItems(0)
                            ->reorderable()
                            ->schema([
                                TextInput::make('count')
                                    ->label('count')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1),
                                TextInput::make('bonus_atk')
                                    ->label('bonus_atk')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('bonus_def')
                                    ->label('bonus_def')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('bonus_hp')
                                    ->label('bonus_hp')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('bonus_boss_dmg')
                                    ->label('bonus_boss_dmg')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
