<?php

namespace App\Filament\Resources\Items\Schemas;

use App\Models\Item;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('基本信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('item_id')
                            ->label('物品ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(Item::class, 'item_id', ignoreRecord: true)
                            ->helperText('物品的唯一标识符，建议使用英文下划线格式'),
                        TextInput::make('item_name')
                            ->label('物品名称')
                            ->required()
                            ->maxLength(100)
                            ->helperText('物品的显示名称'),
                        Select::make('item_type')
                            ->label('物品类型')
                            ->required()
                            ->options(Item::getItemTypeOptions())
                            ->helperText('选择物品的分类类型'),
                        Select::make('rarity')
                            ->label('稀有度')
                            ->required()
                            ->options(Item::getRarityOptions())
                            ->helperText('选择物品的稀有度等级'),
                        TextInput::make('icon')
                            ->label('图标')
                            ->maxLength(100)
                            ->placeholder('icon_gold_coin')
                            ->helperText('图标文件名，不包含扩展名'),
                    ]),
                
                Section::make('描述信息')
                    ->schema([
                        Textarea::make('desc')
                            ->label('物品描述')
                            ->rows(3)
                            ->helperText('物品的详细描述，用于游戏内显示'),
                    ]),
                
                Section::make('状态设置')
                    ->columns(1)
                    ->schema([
                        Toggle::make('is_enabled')
                            ->label('启用状态')
                            ->default(true)
                            ->helperText('是否在游戏中启用此物品'),
                    ]),
            ]);
    }
}
