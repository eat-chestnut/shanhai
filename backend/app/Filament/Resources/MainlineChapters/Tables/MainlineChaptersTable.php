<?php

namespace App\Filament\Resources\MainlineChapters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MainlineChaptersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('chapter_id')
                    ->label('章节ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('chapter_name')
                    ->label('章节名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unlock_level')
                    ->label('开启等级')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('排序')
                    ->sortable(),
                TextColumn::make('required_previous_chapter')
                    ->label('前置章节')
                    ->sortable()
                    ->placeholder('无'),
                TextColumn::make('required_previous_highest_difficulty')
                    ->label('前置难度要求')
                    ->sortable()
                    ->placeholder('无'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
