<?php

namespace App\Filament\Resources\IdleRewardRules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IdleRewardRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('rule_id')
                    ->label('rule_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('rule_name')
                    ->label('rule_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('level_range')
                    ->label('level_range')
                    ->state(static fn ($record): string => sprintf('Lv.%d - Lv.%d', (int) $record->min_level, (int) $record->max_level))
                    ->sortable(query: static fn ($query, string $direction) => $query->orderBy('min_level', $direction)),
                TextColumn::make('idle_cap_hours')
                    ->label('idle_cap_hours')
                    ->suffix('h')
                    ->sortable(),
                TextColumn::make('reward_rate')
                    ->label('reward_rate')
                    ->formatStateUsing(static fn (mixed $state): string => self::formatRewardRate($state))
                    ->wrap(),
                IconColumn::make('is_open')
                    ->label('is_open')
                    ->boolean(),
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

    private static function formatRewardRate(mixed $state): string
    {
        if (! is_array($state) || $state === []) {
            return '-';
        }

        $entries = [];

        foreach ($state as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $itemId = (string) ($entry['item_id'] ?? '');
            $countPerHour = $entry['count_per_hour'] ?? null;

            if ($itemId === '' || $countPerHour === null) {
                continue;
            }

            $entries[] = sprintf('%s x%s/h', $itemId, (string) $countPerHour);
        }

        return $entries === [] ? '-' : implode(' | ', $entries);
    }
}
