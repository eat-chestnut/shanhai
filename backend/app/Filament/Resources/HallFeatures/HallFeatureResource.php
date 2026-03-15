<?php

namespace App\Filament\Resources\HallFeatures;

use App\Filament\Resources\HallFeatures\Pages\CreateHallFeature;
use App\Filament\Resources\HallFeatures\Pages\EditHallFeature;
use App\Filament\Resources\HallFeatures\Pages\ListHallFeatures;
use App\Filament\Resources\HallFeatures\Schemas\HallFeatureForm;
use App\Filament\Resources\HallFeatures\Tables\HallFeaturesTable;
use App\Models\HallFeature;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HallFeatureResource extends Resource
{
    protected static ?string $model = HallFeature::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationLabel(): string
    {
        return '大厅功能';
    }

    public static function getModelLabel(): string
    {
        return '大厅功能';
    }

    public static function getPluralModelLabel(): string
    {
        return '大厅功能';
    }

    public static function form(Schema $schema): Schema
    {
        return HallFeatureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HallFeaturesTable::configure($table);
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
            'index' => ListHallFeatures::route('/'),
            'create' => CreateHallFeature::route('/create'),
            'edit' => EditHallFeature::route('/{record}/edit'),
        ];
    }
}
