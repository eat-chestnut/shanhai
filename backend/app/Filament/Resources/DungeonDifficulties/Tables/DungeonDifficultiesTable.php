<?php

namespace App\Filament\Resources\DungeonDifficulties\Tables;

use App\Models\Dungeon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DungeonDifficultiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('dungeon_id')
            ->columns([
                TextColumn::make('difficulty_id')
                    ->label('difficulty_id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dungeon_id')
                    ->label('dungeon_id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dungeon.dungeon_name')
                    ->label('dungeon_name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('recommended_power')
                    ->label('recommended_power')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('dungeon_id')
                    ->label('dungeon_id')
                    ->options(fn (): array => Dungeon::query()
                        ->orderBy('dungeon_id')
                        ->pluck('dungeon_id', 'dungeon_id')
                        ->all()),
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
