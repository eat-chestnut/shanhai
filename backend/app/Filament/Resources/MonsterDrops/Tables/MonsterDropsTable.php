<?php

namespace App\Filament\Resources\MonsterDrops\Tables;

use App\Enums\MonsterDropKind;
use App\Models\Monster;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MonsterDropsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('monster_id')
            ->columns([
                TextColumn::make('monster_id')
                    ->label('monster_id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('monster.name')
                    ->label('name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('item_id')
                    ->label('item_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('drop_rate')
                    ->label('drop_rate')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('drop_kind')
                    ->label('drop_kind')
                    ->badge()
                    ->formatStateUsing(
                        static fn (string $state): string => MonsterDropKind::tryFrom($state)?->label() ?? $state,
                    )
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('drop_kind')
                    ->label('drop_kind')
                    ->options(MonsterDropKind::options()),
                TernaryFilter::make('monster.is_boss')
                    ->label('monster.is_boss')
                    ->trueLabel('Boss')
                    ->falseLabel('普通怪')
                    ->queries(
                        true: fn ($query) => $query->whereHas('monster', fn ($monsterQuery) => $monsterQuery->where('is_boss', true)),
                        false: fn ($query) => $query->whereHas('monster', fn ($monsterQuery) => $monsterQuery->where('is_boss', false)),
                    ),
                SelectFilter::make('monster_id')
                    ->label('monster_id')
                    ->options(fn (): array => Monster::query()
                        ->orderBy('monster_id')
                        ->pluck('monster_id', 'monster_id')
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
