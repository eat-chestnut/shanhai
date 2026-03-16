<?php

namespace App\Filament\Resources\ScriptureChapterBindings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScriptureChapterBindingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('scripture_id')
                    ->label('经卷ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('chapter_id')
                    ->label('章节ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('排序')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()->label('编辑'),
                DeleteAction::make()->label('删除'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('批量删除'),
                ]),
            ]);
    }
}
