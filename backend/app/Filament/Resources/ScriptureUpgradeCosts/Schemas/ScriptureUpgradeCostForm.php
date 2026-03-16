<?php

namespace App\Filament\Resources\ScriptureUpgradeCosts\Schemas;

use App\Models\Item;
use App\Models\Scripture;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ScriptureUpgradeCostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('升级目标')
                    ->columns(2)
                    ->schema([
                        Select::make('scripture_id')
                            ->label('经卷')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(Scripture::getEnabledScriptureOptions()),
                        TextInput::make('target_world_level')
                            ->label('目标世界等级')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('cost_gold')
                            ->label('金币消耗')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('required_player_level')
                            ->label('所需玩家等级')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                    ]),
                Section::make('材料消耗')
                    ->schema([
                        Repeater::make('cost_items')
                            ->label('消耗材料')
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->schema([
                                Select::make('item_id')
                                    ->label('物品')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->options(Item::getEnabledItemOptions()),
                                TextInput::make('count')
                                    ->label('数量')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1),
                            ])
                            ->columns(2)
                            ->helperText('严格按 JSON 的 cost_items 结构配置。'),
                    ]),
            ]);
    }
}
