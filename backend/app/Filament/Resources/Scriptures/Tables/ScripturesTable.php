<?php

namespace App\Filament\Resources\Scriptures\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScripturesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('scripture_id')
                    ->label('经卷ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('scripture_name')
                    ->label('经卷名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('scripture_group')
                    ->label('经卷分组')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('排序')
                    ->sortable(),
                TextColumn::make('unlock_condition.clear_chapter_id')
                    ->label('通关章节条件')
                    ->placeholder('无'),
                TextColumn::make('unlock_condition.player_level')
                    ->label('等级条件')
                    ->placeholder('无'),
                IconColumn::make('is_enabled')
                    ->label('启用')
                    ->boolean(),
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
