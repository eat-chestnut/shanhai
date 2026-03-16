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
                    ->label('难度ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dungeon_id')
                    ->label('副本ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dungeon.dungeon_name')
                    ->label('副本名称')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('recommended_power')
                    ->label('推荐战力')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('first_clear_reward_group_id')
                    ->label('首通奖励组ID')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('dungeon_id')
                    ->label('副本ID')
                    ->options(fn (): array => Dungeon::query()
                        ->orderBy('dungeon_id')
                        ->pluck('dungeon_id', 'dungeon_id')
                        ->all()),
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
