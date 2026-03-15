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
            ->defaultSort('chapter_id')
            ->columns([
                TextColumn::make('chapter_id')
                    ->label('chapter_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('chapter_name')
                    ->label('chapter_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unlock_level')
                    ->label('unlock_level')
                    ->sortable(),
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
