<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemConfig\Pages\CreateItemConfig;
use App\Filament\Resources\ItemConfig\Pages\EditItemConfig;
use App\Filament\Resources\ItemConfig\Pages\ListItemConfigs;
use App\Models\ItemConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ItemConfigResource extends Resource
{
    protected static ?string $model = ItemConfig::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?int $navigationSort = 60;

    public static function getNavigationGroup(): ?string
    {
        return '物品配置';
    }

    public static function getNavigationLabel(): string
    {
        return '物品管理';
    }

    public static function getModelLabel(): string
    {
        return '物品';
    }

    public static function getPluralModelLabel(): string
    {
        return '物品';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Forms\Components\Section::make('物品信息')
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('item_id')
                            ->label('物品ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        \Filament\Forms\Components\TextInput::make('item_name')
                            ->label('物品名称')
                            ->required()
                            ->maxLength(100),
                        \Filament\Forms\Components\Select::make('item_type')
                            ->label('物品类型')
                            ->required()
                            ->options([
                                'currency' => '货币',
                                'material' => '基础材料',
                                'dungeon_material' => '副本材料',
                                'equipment_material' => '装备材料',
                                'gem' => '宝石',
                                'task_reward' => '任务奖励',
                                'boss_core' => 'Boss核心',
                                'consumable' => '消耗品',
                                'equipment' => '装备',
                                'blue_affix' => '蓝词条',
                                'purple_refinement' => '紫词条',
                            ])
                            ->native(false),
                        \Filament\Forms\Components\Select::make('rarity')
                            ->label('稀有度')
                            ->required()
                            ->relationship('rarityConfig', 'rarity_name')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        \Filament\Forms\Components\TextInput::make('icon')
                            ->label('图标')
                            ->maxLength(200)
                            ->placeholder('item_gold_coin'),
                        \Filament\Forms\Components\Toggle::make('is_enabled')
                            ->label('是否启用')
                            ->default(true),
                    ]),
                \Filament\Forms\Components\Section::make('描述信息')
                    ->schema([
                        \Filament\Forms\Components\Textarea::make('description')
                            ->label('物品描述')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('item_id')
                    ->label('物品ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                \Filament\Tables\Columns\TextColumn::make('item_name')
                    ->label('物品名称')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('item_type')
                    ->label('物品类型')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'currency' => '货币',
                        'material' => '基础材料',
                        'dungeon_material' => '副本材料',
                        'equipment_material' => '装备材料',
                        'gem' => '宝石',
                        'task_reward' => '任务奖励',
                        'boss_core' => 'Boss核心',
                        'consumable' => '消耗品',
                        'equipment' => '装备',
                        'blue_affix' => '蓝词条',
                        'purple_refinement' => '紫词条',
                        default => $state,
                    }),
                \Filament\Tables\Columns\TextColumn::make('rarity')
                    ->label('稀有度')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->rarityConfig?->rarity_name ?? $record->rarity),
                \Filament\Tables\Columns\IconColumn::make('is_enabled')
                    ->label('启用状态')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItemConfigs::route('/'),
            'create' => CreateItemConfig::route('/create'),
            'edit' => EditItemConfig::route('/{record}/edit'),
        ];
    }
}
