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
                    ->label('规则ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('rule_name')
                    ->label('规则名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('level_range')
                    ->label('等级区间')
                    ->state(static fn ($record): string => sprintf('Lv.%d - Lv.%d', (int) $record->min_level, (int) $record->max_level))
                    ->sortable(query: static fn ($query, string $direction) => $query->orderBy('min_level', $direction)),
                TextColumn::make('idle_cap_hours')
                    ->label('挂机上限')
                    ->suffix(' 小时')
                    ->sortable(),
                TextColumn::make('reward_rate')
                    ->label('收益速率')
                    ->formatStateUsing(static fn (mixed $state): string => self::formatRewardRate($state))
                    ->wrap(),
                IconColumn::make('is_open')
                    ->label('是否开启')
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
