<?php

namespace App\Filament\Resources\EquipmentSets;

use App\Filament\Resources\EquipmentSets\Pages\CreateEquipmentSet;
use App\Filament\Resources\EquipmentSets\Pages\EditEquipmentSet;
use App\Filament\Resources\EquipmentSets\Pages\ListEquipmentSets;
use App\Filament\Resources\EquipmentSets\Schemas\EquipmentSetForm;
use App\Filament\Resources\EquipmentSets\Tables\EquipmentSetsTable;
use App\Models\EquipmentSet;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EquipmentSetResource extends Resource
{
    protected static ?string $model = EquipmentSet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 41;

    public static function getNavigationGroup(): ?string
    {
        return '装备配置';
    }

    public static function getNavigationLabel(): string
    {
        return '套装管理';
    }

    public static function getModelLabel(): string
    {
        return '套装';
    }

    public static function getPluralModelLabel(): string
    {
        return '套装';
    }

    public static function form(Schema $schema): Schema
    {
        return EquipmentSetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EquipmentSetsTable::configure($table);
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
            'index' => ListEquipmentSets::route('/'),
            'create' => CreateEquipmentSet::route('/create'),
            'edit' => EditEquipmentSet::route('/{record}/edit'),
        ];
    }
}
