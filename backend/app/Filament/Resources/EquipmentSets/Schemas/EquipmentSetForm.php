<?php

namespace App\Filament\Resources\EquipmentSets\Schemas;

use App\Models\Equipment;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as FormSection;
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
                            ->label('套装ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('level')
                            ->label('套装等级')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        TagsInput::make('pieces')
                            ->label('套装部件')
                            ->required()
                            ->reorderable()
                            ->suggestions(fn (): array => Equipment::query()->orderBy('equip_id')->pluck('equip_id')->all())
                            ->columnSpanFull(),
                        Repeater::make('effects')
                            ->label('套装效果')
                            ->defaultItems(0)
                            ->reorderable()
                            ->schema([
                                TextInput::make('count')
                                    ->label('件数')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1),
                                TextInput::make('bonus_atk')
                                    ->label('攻击加成')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('bonus_def')
                                    ->label('防御加成')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('bonus_hp')
                                    ->label('生命加成')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('bonus_boss_dmg')
                                    ->label('Boss伤害加成')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('bonus_attack_speed')
                                    ->label('攻速加成')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('bonus_damage_ratio')
                                    ->label('伤害倍率加成')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
