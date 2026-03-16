<?php

namespace App\Filament\Resources\CharacterClasses;

use App\Filament\Resources\CharacterClasses\Pages\CreateCharacterClass;
use App\Filament\Resources\CharacterClasses\Pages\EditCharacterClass;
use App\Filament\Resources\CharacterClasses\Pages\ListCharacterClasses;
use App\Filament\Resources\CharacterClasses\Schemas\CharacterClassForm;
use App\Filament\Resources\CharacterClasses\Tables\CharacterClassesTable;
use App\Models\CharacterClass;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CharacterClassResource extends Resource
{
    protected static ?string $model = CharacterClass::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationLabel(): string
    {
        return '职业管理';
    }

    public static function getNavigationGroup(): ?string
    {
        return '角色配置';
    }

    public static function getModelLabel(): string
    {
        return '职业';
    }

    public static function getPluralModelLabel(): string
    {
        return '职业';
    }

    public static function form(Schema $schema): Schema
    {
        return CharacterClassForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CharacterClassesTable::configure($table);
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
            'index' => ListCharacterClasses::route('/'),
            'create' => CreateCharacterClass::route('/create'),
            'edit' => EditCharacterClass::route('/{record}/edit'),
        ];
    }
}
