<?php

namespace App\Filament\Resources\BlueAffixes;

use App\Filament\Resources\BlueAffixes\Pages\CreateBlueAffix;
use App\Filament\Resources\BlueAffixes\Pages\EditBlueAffix;
use App\Filament\Resources\BlueAffixes\Pages\ListBlueAffixes;
use App\Filament\Resources\BlueAffixes\Schemas\BlueAffixForm;
use App\Filament\Resources\BlueAffixes\Tables\BlueAffixesTable;
use App\Models\BlueAffix;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BlueAffixResource extends Resource
{
    protected static ?string $model = BlueAffix::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 43;

    public static function getNavigationGroup(): ?string
    {
        return '装备配置';
    }

    public static function getNavigationLabel(): string
    {
        return '蓝词条';
    }

    public static function getModelLabel(): string
    {
        return '蓝词条';
    }

    public static function getPluralModelLabel(): string
    {
        return '蓝词条';
    }

    public static function form(Schema $schema): Schema
    {
        return BlueAffixForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BlueAffixesTable::configure($table);
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
            'index' => ListBlueAffixes::route('/'),
            'create' => CreateBlueAffix::route('/create'),
            'edit' => EditBlueAffix::route('/{record}/edit'),
        ];
    }
}
