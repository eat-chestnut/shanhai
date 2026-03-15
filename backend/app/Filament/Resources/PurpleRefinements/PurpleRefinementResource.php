<?php

namespace App\Filament\Resources\PurpleRefinements;

use App\Filament\Resources\PurpleRefinements\Pages\CreatePurpleRefinement;
use App\Filament\Resources\PurpleRefinements\Pages\EditPurpleRefinement;
use App\Filament\Resources\PurpleRefinements\Pages\ListPurpleRefinements;
use App\Filament\Resources\PurpleRefinements\Schemas\PurpleRefinementForm;
use App\Filament\Resources\PurpleRefinements\Tables\PurpleRefinementsTable;
use App\Models\PurpleRefinement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PurpleRefinementResource extends Resource
{
    protected static ?string $model = PurpleRefinement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 44;

    public static function getNavigationGroup(): ?string
    {
        return '装备配置';
    }

    public static function getNavigationLabel(): string
    {
        return '紫洗练';
    }

    public static function getModelLabel(): string
    {
        return '紫洗练';
    }

    public static function getPluralModelLabel(): string
    {
        return '紫洗练';
    }

    public static function form(Schema $schema): Schema
    {
        return PurpleRefinementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurpleRefinementsTable::configure($table);
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
            'index' => ListPurpleRefinements::route('/'),
            'create' => CreatePurpleRefinement::route('/create'),
            'edit' => EditPurpleRefinement::route('/{record}/edit'),
        ];
    }
}
