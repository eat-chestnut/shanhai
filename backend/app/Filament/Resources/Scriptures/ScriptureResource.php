<?php

namespace App\Filament\Resources\Scriptures;

use App\Filament\Resources\Scriptures\Pages\CreateScripture;
use App\Filament\Resources\Scriptures\Pages\EditScripture;
use App\Filament\Resources\Scriptures\Pages\ListScriptures;
use App\Filament\Resources\Scriptures\Schemas\ScriptureForm;
use App\Filament\Resources\Scriptures\Tables\ScripturesTable;
use App\Models\Scripture;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScriptureResource extends Resource
{
    protected static ?string $model = Scripture::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?int $navigationSort = 40;

    public static function getNavigationLabel(): string
    {
        return '经卷管理';
    }

    public static function getNavigationGroup(): ?string
    {
        return '经卷回刷';
    }

    public static function getModelLabel(): string
    {
        return '经卷';
    }

    public static function getPluralModelLabel(): string
    {
        return '经卷';
    }

    public static function form(Schema $schema): Schema
    {
        return ScriptureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScripturesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScriptures::route('/'),
            'create' => CreateScripture::route('/create'),
            'edit' => EditScripture::route('/{record}/edit'),
        ];
    }
}
