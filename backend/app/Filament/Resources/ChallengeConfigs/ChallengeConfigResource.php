<?php

namespace App\Filament\Resources\ChallengeConfigs;

use App\Filament\Resources\ChallengeConfigs\Pages\CreateChallengeConfig;
use App\Filament\Resources\ChallengeConfigs\Pages\EditChallengeConfig;
use App\Filament\Resources\ChallengeConfigs\Pages\ListChallengeConfigs;
use App\Filament\Resources\ChallengeConfigs\Schemas\ChallengeConfigForm;
use App\Filament\Resources\ChallengeConfigs\Tables\ChallengeConfigsTable;
use App\Models\ChallengeConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChallengeConfigResource extends Resource
{
    protected static ?string $model = ChallengeConfig::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationLabel(): string
    {
        return '长线挑战';
    }

    public static function getModelLabel(): string
    {
        return '长线挑战';
    }

    public static function getPluralModelLabel(): string
    {
        return '长线挑战';
    }

    public static function form(Schema $schema): Schema
    {
        return ChallengeConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChallengeConfigsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChallengeConfigs::route('/'),
            'create' => CreateChallengeConfig::route('/create'),
            'edit' => EditChallengeConfig::route('/{record}/edit'),
        ];
    }
}
