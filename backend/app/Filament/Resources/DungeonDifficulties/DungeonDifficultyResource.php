<?php

namespace App\Filament\Resources\DungeonDifficulties;

use App\Filament\Resources\DungeonDifficulties\Pages\CreateDungeonDifficulty;
use App\Filament\Resources\DungeonDifficulties\Pages\EditDungeonDifficulty;
use App\Filament\Resources\DungeonDifficulties\Pages\ListDungeonDifficulties;
use App\Filament\Resources\DungeonDifficulties\Schemas\DungeonDifficultyForm;
use App\Filament\Resources\DungeonDifficulties\Tables\DungeonDifficultiesTable;
use App\Models\DungeonDifficulty;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DungeonDifficultyResource extends Resource
{
    protected static ?string $model = DungeonDifficulty::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 31;

    public static function getNavigationGroup(): ?string
    {
        return '副本配置';
    }

    public static function getNavigationLabel(): string
    {
        return '副本难度';
    }

    public static function getModelLabel(): string
    {
        return '副本难度';
    }

    public static function getPluralModelLabel(): string
    {
        return '副本难度';
    }

    public static function form(Schema $schema): Schema
    {
        return DungeonDifficultyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DungeonDifficultiesTable::configure($table);
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
            'index' => ListDungeonDifficulties::route('/'),
            'create' => CreateDungeonDifficulty::route('/create'),
            'edit' => EditDungeonDifficulty::route('/{record}/edit'),
        ];
    }
}
