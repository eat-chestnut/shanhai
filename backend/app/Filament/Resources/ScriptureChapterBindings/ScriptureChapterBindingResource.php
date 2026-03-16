<?php

namespace App\Filament\Resources\ScriptureChapterBindings;

use App\Filament\Resources\ScriptureChapterBindings\Pages\CreateScriptureChapterBinding;
use App\Filament\Resources\ScriptureChapterBindings\Pages\EditScriptureChapterBinding;
use App\Filament\Resources\ScriptureChapterBindings\Pages\ListScriptureChapterBindings;
use App\Filament\Resources\ScriptureChapterBindings\Schemas\ScriptureChapterBindingForm;
use App\Filament\Resources\ScriptureChapterBindings\Tables\ScriptureChapterBindingsTable;
use App\Models\ScriptureChapterBinding;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScriptureChapterBindingResource extends Resource
{
    protected static ?string $model = ScriptureChapterBinding::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?int $navigationSort = 41;

    public static function getNavigationLabel(): string
    {
        return '经卷章节绑定';
    }

    public static function getNavigationGroup(): ?string
    {
        return '经卷回刷';
    }

    public static function getModelLabel(): string
    {
        return '经卷章节绑定';
    }

    public static function getPluralModelLabel(): string
    {
        return '经卷章节绑定';
    }

    public static function form(Schema $schema): Schema
    {
        return ScriptureChapterBindingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScriptureChapterBindingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScriptureChapterBindings::route('/'),
            'create' => CreateScriptureChapterBinding::route('/create'),
            'edit' => EditScriptureChapterBinding::route('/{record}/edit'),
        ];
    }
}
