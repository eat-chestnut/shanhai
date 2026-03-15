<?php

namespace App\Filament\Resources\MainlineChapters;

use App\Filament\Resources\MainlineChapters\Pages\CreateMainlineChapter;
use App\Filament\Resources\MainlineChapters\Pages\EditMainlineChapter;
use App\Filament\Resources\MainlineChapters\Pages\ListMainlineChapters;
use App\Filament\Resources\MainlineChapters\Schemas\MainlineChapterForm;
use App\Filament\Resources\MainlineChapters\Tables\MainlineChaptersTable;
use App\Models\MainlineChapter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MainlineChapterResource extends Resource
{
    protected static ?string $model = MainlineChapter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 20;

    public static function getNavigationLabel(): string
    {
        return '章节管理';
    }

    public static function getNavigationGroup(): ?string
    {
        return '主线配置';
    }

    public static function getModelLabel(): string
    {
        return '章节';
    }

    public static function getPluralModelLabel(): string
    {
        return '章节';
    }

    public static function form(Schema $schema): Schema
    {
        return MainlineChapterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MainlineChaptersTable::configure($table);
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
            'index' => ListMainlineChapters::route('/'),
            'create' => CreateMainlineChapter::route('/create'),
            'edit' => EditMainlineChapter::route('/{record}/edit'),
        ];
    }
}
