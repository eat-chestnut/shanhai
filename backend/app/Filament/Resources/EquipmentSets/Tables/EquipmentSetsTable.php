<?php

namespace App\Filament\Resources\EquipmentSets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EquipmentSetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('set_id')
            ->columns([
                TextColumn::make('set_id')
                    ->label('set_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('level')
                    ->label('level')
                    ->sortable(),
                TextColumn::make('pieces')
                    ->label('pieces')
                    ->formatStateUsing(static fn (?array $state): string => implode(', ', $state ?? []))
                    ->wrap(),
                TextColumn::make('effects')
                    ->label('effects')
                    ->formatStateUsing(static fn (?array $state): string => self::formatEffects($state))
                    ->wrap(),
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

    /**
     * @param  array<int, array<string, mixed>>|null  $effects
     */
    private static function formatEffects(?array $effects): string
    {
        if ($effects === null || $effects === []) {
            return '-';
        }

        return collect($effects)
            ->map(static function (array $effect): string {
                $parts = [];

                foreach (['bonus_atk', 'bonus_def', 'bonus_hp', 'bonus_boss_dmg'] as $field) {
                    if (filled($effect[$field] ?? null) && (int) $effect[$field] !== 0) {
                        $parts[] = "{$field}+{$effect[$field]}";
                    }
                }

                return "{$effect['count']}件: ".implode(', ', $parts);
            })
            ->implode(' | ');
    }
}
