<?php

namespace App\Filament\Resources\Monsters;

use App\Filament\Resources\Monsters\Pages\CreateMonster;
use App\Filament\Resources\Monsters\Pages\EditMonster;
use App\Filament\Resources\Monsters\Pages\ListMonsters;
use App\Filament\Resources\Monsters\Schemas\MonsterForm;
use App\Filament\Resources\Monsters\Tables\MonstersTable;
use App\Models\Monster;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MonsterResource extends Resource
{
    protected static ?string $model = Monster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 32;

    public static function getNavigationGroup(): ?string
    {
        return '副本配置';
    }

    public static function getNavigationLabel(): string
    {
        return '怪物管理';
    }

    public static function getModelLabel(): string
    {
        return '怪物';
    }

    public static function getPluralModelLabel(): string
    {
        return '怪物';
    }

    public static function form(Schema $schema): Schema
    {
        return MonsterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MonstersTable::configure($table);
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
            'index' => ListMonsters::route('/'),
            'create' => CreateMonster::route('/create'),
            'edit' => EditMonster::route('/{record}/edit'),
        ];
    }
}
