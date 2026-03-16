<?php

namespace App\Filament\Resources\ScriptureWorldTiers;

use App\Filament\Resources\ScriptureWorldTiers\Pages\CreateScriptureWorldTier;
use App\Filament\Resources\ScriptureWorldTiers\Pages\EditScriptureWorldTier;
use App\Filament\Resources\ScriptureWorldTiers\Pages\ListScriptureWorldTiers;
use App\Filament\Resources\ScriptureWorldTiers\Schemas\ScriptureWorldTierForm;
use App\Filament\Resources\ScriptureWorldTiers\Tables\ScriptureWorldTiersTable;
use App\Models\ScriptureWorldTier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScriptureWorldTierResource extends Resource
{
    protected static ?string $model = ScriptureWorldTier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?int $navigationSort = 42;

    public static function getNavigationLabel(): string
    {
        return '经卷世界等级';
    }

    public static function getNavigationGroup(): ?string
    {
        return '经卷回刷';
    }

    public static function getModelLabel(): string
    {
        return '世界等级区间';
    }

    public static function getPluralModelLabel(): string
    {
        return '世界等级区间';
    }

    public static function form(Schema $schema): Schema
    {
        return ScriptureWorldTierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScriptureWorldTiersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScriptureWorldTiers::route('/'),
            'create' => CreateScriptureWorldTier::route('/create'),
            'edit' => EditScriptureWorldTier::route('/{record}/edit'),
        ];
    }
}
