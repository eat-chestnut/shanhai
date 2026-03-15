<?php

namespace App\Filament\Resources\Gems;

use App\Filament\Resources\Gems\Pages\CreateGem;
use App\Filament\Resources\Gems\Pages\EditGem;
use App\Filament\Resources\Gems\Pages\ListGems;
use App\Filament\Resources\Gems\Schemas\GemForm;
use App\Filament\Resources\Gems\Tables\GemsTable;
use App\Models\Gem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GemResource extends Resource
{
    protected static ?string $model = Gem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 42;

    public static function getNavigationGroup(): ?string
    {
        return '装备配置';
    }

    public static function getNavigationLabel(): string
    {
        return '宝石管理';
    }

    public static function getModelLabel(): string
    {
        return '宝石';
    }

    public static function getPluralModelLabel(): string
    {
        return '宝石';
    }

    public static function form(Schema $schema): Schema
    {
        return GemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GemsTable::configure($table);
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
            'index' => ListGems::route('/'),
            'create' => CreateGem::route('/create'),
            'edit' => EditGem::route('/{record}/edit'),
        ];
    }
}
