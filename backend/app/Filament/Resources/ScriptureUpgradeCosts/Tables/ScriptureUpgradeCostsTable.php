<?php

namespace App\Filament\Resources\ScriptureUpgradeCosts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScriptureUpgradeCostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('target_world_level')
            ->columns([
                TextColumn::make('scripture_id')
                    ->label('经卷ID')
                    ->sortable(),
                TextColumn::make('target_world_level')
                    ->label('目标等级')
                    ->sortable(),
                TextColumn::make('cost_gold')
                    ->label('金币消耗')
                    ->sortable(),
                TextColumn::make('required_player_level')
                    ->label('所需等级')
                    ->sortable(),
                TextColumn::make('cost_items')
                    ->label('材料消耗')
                    ->formatStateUsing(static function (mixed $state): string {
                        if (! is_array($state) || $state === []) {
                            return '无';
                        }

                        return collect($state)
                            ->map(static fn (array $entry): string => sprintf('%s x%d', (string) ($entry['item_id'] ?? ''), (int) ($entry['count'] ?? 0)))
                            ->implode('，');
                    })
                    ->wrap(),
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
