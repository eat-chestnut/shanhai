<?php

namespace App\Filament\Resources\MonsterDrops;

use App\Filament\Resources\MonsterDrops\Pages\CreateMonsterDrop;
use App\Filament\Resources\MonsterDrops\Pages\EditMonsterDrop;
use App\Filament\Resources\MonsterDrops\Pages\ListMonsterDrops;
use App\Filament\Resources\MonsterDrops\Schemas\MonsterDropForm;
use App\Filament\Resources\MonsterDrops\Tables\MonsterDropsTable;
use App\Models\MonsterDrop;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MonsterDropResource extends Resource
{
    protected static ?string $model = MonsterDrop::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 33;

    public static function getNavigationGroup(): ?string
    {
        return '副本配置';
    }

    public static function getNavigationLabel(): string
    {
        return '怪物掉落';
    }

    public static function getModelLabel(): string
    {
        return '怪物掉落';
    }

    public static function getPluralModelLabel(): string
    {
        return '怪物掉落';
    }

    public static function form(Schema $schema): Schema
    {
        return MonsterDropForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MonsterDropsTable::configure($table);
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
            'index' => ListMonsterDrops::route('/'),
            'create' => CreateMonsterDrop::route('/create'),
            'edit' => EditMonsterDrop::route('/{record}/edit'),
        ];
    }
}
