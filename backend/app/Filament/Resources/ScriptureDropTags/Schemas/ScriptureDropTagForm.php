<?php

namespace App\Filament\Resources\ScriptureDropTags\Schemas;

use App\Models\Item;
use App\Models\ScriptureDropTag;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ScriptureDropTagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('标签信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('drop_tag')
                            ->label('掉落标签')
                            ->required()
                            ->maxLength(100)
                            ->unique(ScriptureDropTag::class, 'drop_tag', ignoreRecord: true),
                        TextInput::make('tag_name')
                            ->label('标签名称')
                            ->required()
                            ->maxLength(100),
                    ]),
                Section::make('掉落项')
                    ->schema([
                        Repeater::make('items')
                            ->label('物品组')
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
                                TextInput::make('weight')
                                    ->label('权重')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1),
                                TextInput::make('min')
                                    ->label('最小数量')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1),
                                TextInput::make('max')
                                    ->label('最大数量')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1),
                            ])
                            ->columns(4)
                            ->helperText('保持 drop tag.items 的正式结构，不做改层或改名。'),
                    ]),
            ]);
    }
}
