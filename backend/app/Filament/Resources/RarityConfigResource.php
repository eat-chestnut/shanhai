<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RarityConfig\Pages\CreateRarityConfig;
use App\Filament\Resources\RarityConfig\Pages\EditRarityConfig;
use App\Filament\Resources\RarityConfig\Pages\ListRarityConfigs;
use App\Models\RarityConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RarityConfigResource extends Resource
{
    protected static ?string $model = RarityConfig::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 61;

    public static function getNavigationGroup(): ?string
    {
        return '物品配置';
    }

    public static function getNavigationLabel(): string
    {
        return '稀有度配置';
    }

    public static function getModelLabel(): string
    {
        return '稀有度';
    }

    public static function getPluralModelLabel(): string
    {
        return '稀有度';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Forms\Components\Section::make('稀有度信息')
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('rarity_key')
                            ->label('稀有度标识')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->helperText('程序使用的唯一标识，如：common, rare, epic, legendary'),
                        \Filament\Forms\Components\TextInput::make('rarity_name')
                            ->label('稀有度名称')
                            ->required()
                            ->maxLength(50)
                            ->helperText('运营显示的中文名称，如：普通、稀有、史诗、传说'),
                        \Filament\Forms\Components\TextInput::make('sort_order')
                            ->label('排序')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->helperText('数值越小排序越靠前'),
                        \Filament\Forms\Components\Toggle::make('is_enabled')
                            ->label('是否启用')
                            ->default(true),
                    ]),
                \Filament\Forms\Components\Section::make('样式配置')
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('text_color')
                            ->label('文字颜色')
                            ->maxLength(20)
                            ->placeholder('#FFFFFF')
                            ->helperText('十六进制颜色值'),
                        \Filament\Forms\Components\TextInput::make('bg_color')
                            ->label('背景颜色')
                            ->maxLength(20)
                            ->placeholder('#000000')
                            ->helperText('十六进制颜色值'),
                        \Filament\Forms\Components\TextInput::make('border_color')
                            ->label('边框颜色')
                            ->maxLength(20)
                            ->placeholder('#FFD700')
                            ->helperText('十六进制颜色值'),
                        \Filament\Forms\Components\TextInput::make('glow_color')
                            ->label('发光颜色')
                            ->maxLength(20)
                            ->placeholder('#FFD700')
                            ->helperText('十六进制颜色值'),
                        \Filament\Forms\Components\TextInput::make('frame_key')
                            ->label('边框资源Key')
                            ->maxLength(100)
                            ->placeholder('frame_legendary')
                            ->helperText('客户端边框资源标识')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('rarity_key')
                    ->label('稀有度标识')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                \Filament\Tables\Columns\TextColumn::make('rarity_name')
                    ->label('稀有度名称')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('sort_order')
                    ->label('排序')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('text_color')
                    ->label('文字颜色')
                    ->placeholder('默认')
                    ->toggleable()
                    ->formatStateUsing(fn ($state) => $state ? "<span style='color: {$state}'>{$state}</span>" : '默认')
                    ->html(),
                \Filament\Tables\Columns\TextColumn::make('bg_color')
                    ->label('背景颜色')
                    ->placeholder('默认')
                    ->toggleable()
                    ->formatStateUsing(fn ($state) => $state ? "<span style='background-color: {$state}; color: white; padding: 2px 6px; border-radius: 3px;'>{$state}</span>" : '默认')
                    ->html(),
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
            'index' => ListRarityConfigs::route('/'),
            'create' => CreateRarityConfig::route('/create'),
            'edit' => EditRarityConfig::route('/{record}/edit'),
        ];
    }
}
