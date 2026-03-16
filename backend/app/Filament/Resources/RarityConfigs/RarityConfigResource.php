<?php

namespace App\Filament\Resources\RarityConfigs;

use App\Filament\Resources\RarityConfigs\Pages\CreateRarityConfig;
use App\Filament\Resources\RarityConfigs\Pages\EditRarityConfig;
use App\Filament\Resources\RarityConfigs\Pages\ListRarityConfigs;
use App\Filament\Resources\RarityConfigs\Schemas\RarityConfigForm;
use App\Filament\Resources\RarityConfigs\Tables\RarityConfigsTable;
use App\Models\RarityConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class RarityConfigResource extends Resource
{
    protected static ?string $model = RarityConfig::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 31;

    public static function getNavigationLabel(): string
    {
        return '稀有度配置';
    }

    public static function getNavigationGroup(): ?string
    {
        return '游戏配置';
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
        return RarityConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RarityConfigsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
