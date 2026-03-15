<?php

namespace App\Filament\Resources\Dungeons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DungeonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('dungeon_id')
            ->columns([
                TextColumn::make('dungeon_id')
                    ->label('dungeon_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('dungeon_name')
                    ->label('dungeon_name')
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
