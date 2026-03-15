<?php

namespace App\Filament\Resources\Dungeons;

use App\Filament\Resources\Dungeons\Pages\CreateDungeon;
use App\Filament\Resources\Dungeons\Pages\EditDungeon;
use App\Filament\Resources\Dungeons\Pages\ListDungeons;
use App\Filament\Resources\Dungeons\Schemas\DungeonForm;
use App\Filament\Resources\Dungeons\Tables\DungeonsTable;
use App\Models\Dungeon;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DungeonResource extends Resource
{
    protected static ?string $model = Dungeon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 30;

    public static function getNavigationGroup(): ?string
    {
        return '副本配置';
    }

    public static function getNavigationLabel(): string
    {
        return '副本管理';
    }

    public static function getModelLabel(): string
    {
        return '副本';
    }

    public static function getPluralModelLabel(): string
    {
        return '副本';
    }

    public static function form(Schema $schema): Schema
    {
        return DungeonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DungeonsTable::configure($table);
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
            'index' => ListDungeons::route('/'),
            'create' => CreateDungeon::route('/create'),
            'edit' => EditDungeon::route('/{record}/edit'),
        ];
    }
}
