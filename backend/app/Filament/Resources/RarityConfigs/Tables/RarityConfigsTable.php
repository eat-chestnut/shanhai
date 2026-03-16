<?php

namespace App\Filament\Resources\RarityConfigs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

class RarityConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort', 'asc')
            ->columns([
                TextColumn::make('rarity_key')
                    ->label('稀有度键值')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('rarity_name')
                    ->label('稀有度名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sort')
                    ->label('排序')
                    ->sortable()
                    ->alignCenter(),
                ColorColumn::make('text_color')
                    ->label('文字颜色')
                    ->sortable(),
                ColorColumn::make('bg_color')
                    ->label('背景颜色')
                    ->sortable(),
                ColorColumn::make('border_color')
                    ->label('边框颜色')
                    ->sortable(),
                TextColumn::make('frame_key')
                    ->label('边框样式')
                    ->searchable()
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
