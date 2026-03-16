<?php

namespace App\Filament\Resources\Items\Tables;

use App\Models\Item;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

class ItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('item_type', 'asc')
            ->columns([
                TextColumn::make('item_id')
                    ->label('物品ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('item_name')
                    ->label('物品名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('item_type')
                    ->label('物品类型')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => Item::getItemTypeOptions()[$state] ?? $state),
                TextColumn::make('rarity')
                    ->label('稀有度')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => Item::getRarityOptions()[$state] ?? $state),
                TextColumn::make('icon')
                    ->label('图标')
                    ->searchable()
                    ->placeholder('无'),
                TextColumn::make('desc')
                    ->label('描述')
                    ->limit(50)
                    ->placeholder('无'),
                ToggleColumn::make('is_enabled')
                    ->label('启用')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->label('编辑'),
                DeleteAction::make()->label('删除'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('批量删除'),
                ]),
            ]);
    }
}
