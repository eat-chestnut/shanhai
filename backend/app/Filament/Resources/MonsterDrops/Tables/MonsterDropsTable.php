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
                    ->label('怪物ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('monster.name')
                    ->label('怪物名称')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('item.item_name')
                    ->label('物品名称')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('item_id')
                    ->label('物品ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('drop_rate')
                    ->label('掉落概率')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('drop_kind')
                    ->label('掉落类型')
                    ->badge()
                    ->formatStateUsing(
                        static fn (string $state): string => MonsterDropKind::tryFrom($state)?->label() ?? $state,
                    )
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('drop_kind')
                    ->label('掉落类型')
                    ->options(MonsterDropKind::options()),
                TernaryFilter::make('monster.is_boss')
                    ->label('是否 Boss')
                    ->trueLabel('Boss')
                    ->falseLabel('普通怪')
                    ->queries(
                        true: fn ($query) => $query->whereHas('monster', fn ($monsterQuery) => $monsterQuery->where('is_boss', true)),
                        false: fn ($query) => $query->whereHas('monster', fn ($monsterQuery) => $monsterQuery->where('is_boss', false)),
                    ),
                SelectFilter::make('monster_id')
                    ->label('怪物ID')
                    ->options(fn (): array => Monster::query()
                        ->orderBy('monster_id')
                        ->pluck('monster_id', 'monster_id')
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
