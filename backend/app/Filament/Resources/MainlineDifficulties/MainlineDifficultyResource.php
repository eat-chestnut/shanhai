<?php

namespace App\Filament\Resources\MainlineDifficulties;

use App\Filament\Resources\MainlineDifficulties\Pages\CreateMainlineDifficulty;
use App\Filament\Resources\MainlineDifficulties\Pages\EditMainlineDifficulty;
use App\Filament\Resources\MainlineDifficulties\Pages\ListMainlineDifficulties;
use App\Filament\Resources\MainlineDifficulties\Schemas\MainlineDifficultyForm;
use App\Filament\Resources\MainlineDifficulties\Tables\MainlineDifficultiesTable;
use App\Models\MainlineDifficulty;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MainlineDifficultyResource extends Resource
{
    protected static ?string $model = MainlineDifficulty::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 22;

    public static function getNavigationLabel(): string
    {
        return '难度管理';
    }

    public static function getNavigationGroup(): ?string
    {
        return '主线配置';
    }

    public static function getModelLabel(): string
    {
        return '难度';
    }

    public static function getPluralModelLabel(): string
    {
        return '难度';
    }

    public static function form(Schema $schema): Schema
    {
        return MainlineDifficultyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MainlineDifficultiesTable::configure($table);
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
            'index' => ListMainlineDifficulties::route('/'),
            'create' => CreateMainlineDifficulty::route('/create'),
            'edit' => EditMainlineDifficulty::route('/{record}/edit'),
        ];
    }
}
