<?php

namespace App\Filament\Resources\ScriptureDropTags;

use App\Filament\Resources\ScriptureDropTags\Pages\CreateScriptureDropTag;
use App\Filament\Resources\ScriptureDropTags\Pages\EditScriptureDropTag;
use App\Filament\Resources\ScriptureDropTags\Pages\ListScriptureDropTags;
use App\Filament\Resources\ScriptureDropTags\Schemas\ScriptureDropTagForm;
use App\Filament\Resources\ScriptureDropTags\Tables\ScriptureDropTagsTable;
use App\Models\ScriptureDropTag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScriptureDropTagResource extends Resource
{
    protected static ?string $model = ScriptureDropTag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGiftTop;

    protected static ?int $navigationSort = 45;

    public static function getNavigationLabel(): string
    {
        return '经卷掉落标签';
    }

    public static function getNavigationGroup(): ?string
    {
        return '经卷回刷';
    }

    public static function getModelLabel(): string
    {
        return '掉落标签';
    }

    public static function getPluralModelLabel(): string
    {
        return '掉落标签';
    }

    public static function form(Schema $schema): Schema
    {
        return ScriptureDropTagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScriptureDropTagsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScriptureDropTags::route('/'),
            'create' => CreateScriptureDropTag::route('/create'),
            'edit' => EditScriptureDropTag::route('/{record}/edit'),
        ];
    }
}
