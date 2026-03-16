<?php

namespace App\Filament\Resources\ScriptureUpgradeCosts;

use App\Filament\Resources\ScriptureUpgradeCosts\Pages\CreateScriptureUpgradeCost;
use App\Filament\Resources\ScriptureUpgradeCosts\Pages\EditScriptureUpgradeCost;
use App\Filament\Resources\ScriptureUpgradeCosts\Pages\ListScriptureUpgradeCosts;
use App\Filament\Resources\ScriptureUpgradeCosts\Schemas\ScriptureUpgradeCostForm;
use App\Filament\Resources\ScriptureUpgradeCosts\Tables\ScriptureUpgradeCostsTable;
use App\Models\ScriptureUpgradeCost;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScriptureUpgradeCostResource extends Resource
{
    protected static ?string $model = ScriptureUpgradeCost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 43;

    public static function getNavigationLabel(): string
    {
        return '经卷升级成本';
    }

    public static function getNavigationGroup(): ?string
    {
        return '经卷回刷';
    }

    public static function getModelLabel(): string
    {
        return '升级成本';
    }

    public static function getPluralModelLabel(): string
    {
        return '升级成本';
    }

    public static function form(Schema $schema): Schema
    {
        return ScriptureUpgradeCostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScriptureUpgradeCostsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScriptureUpgradeCosts::route('/'),
            'create' => CreateScriptureUpgradeCost::route('/create'),
            'edit' => EditScriptureUpgradeCost::route('/{record}/edit'),
        ];
    }
}
