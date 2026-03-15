<?php

namespace App\Filament\Resources\MainlineNodes;

use App\Filament\Resources\MainlineNodes\Pages\CreateMainlineNode;
use App\Filament\Resources\MainlineNodes\Pages\EditMainlineNode;
use App\Filament\Resources\MainlineNodes\Pages\ListMainlineNodes;
use App\Filament\Resources\MainlineNodes\Schemas\MainlineNodeForm;
use App\Filament\Resources\MainlineNodes\Tables\MainlineNodesTable;
use App\Models\MainlineNode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MainlineNodeResource extends Resource
{
    protected static ?string $model = MainlineNode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 21;

    public static function getNavigationLabel(): string
    {
        return '节点管理';
    }

    public static function getNavigationGroup(): ?string
    {
        return '主线配置';
    }

    public static function getModelLabel(): string
    {
        return '节点';
    }

    public static function getPluralModelLabel(): string
    {
        return '节点';
    }

    public static function form(Schema $schema): Schema
    {
        return MainlineNodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MainlineNodesTable::configure($table);
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
            'index' => ListMainlineNodes::route('/'),
            'create' => CreateMainlineNode::route('/create'),
            'edit' => EditMainlineNode::route('/{record}/edit'),
        ];
    }
}
