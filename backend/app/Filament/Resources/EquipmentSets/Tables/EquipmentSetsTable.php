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
                    ->label('套装ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('level')
                    ->label('套装等级')
                    ->sortable(),
                TextColumn::make('pieces')
                    ->label('套装部件')
                    ->formatStateUsing(static fn (mixed $state): string => self::formatPieces($state))
                    ->wrap(),
                TextColumn::make('effects')
                    ->label('套装效果')
                    ->formatStateUsing(static fn (mixed $state): string => self::formatEffects($state))
                    ->wrap(),
            ])
            ->filters([
                //
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

    private static function formatPieces(mixed $state): string
    {
        if (is_array($state)) {
            return implode(', ', $state);
        }

        return filled($state) ? (string) $state : '-';
    }

    private static function formatEffects(mixed $effects): string
    {
        if (! is_array($effects)) {
            return filled($effects) ? (string) $effects : '-';
        }

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
