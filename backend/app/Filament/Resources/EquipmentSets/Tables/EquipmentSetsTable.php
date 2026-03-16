<?php

namespace App\Filament\Resources\EquipmentSets\Tables;

use App\Models\EquipmentSet;
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

        $normalizedEffects = EquipmentSet::normalizeEffectsPayload($effects);

        if ($normalizedEffects === []) {
            return '-';
        }

        return collect($normalizedEffects)
            ->map(static function (array $effect): string {
                $parts = [];

                $labels = [
                    'bonus_atk' => '攻击',
                    'bonus_def' => '防御',
                    'bonus_hp' => '生命',
                    'bonus_boss_dmg' => 'Boss伤害',
                    'bonus_attack_speed' => '攻速',
                    'bonus_damage_ratio' => '伤害倍率',
                ];

                foreach ($labels as $field => $label) {
                    $value = $effect[$field] ?? null;

                    if (! filled($value)) {
                        continue;
                    }

                    if ((float) $value == 0.0) {
                        continue;
                    }

                    $parts[] = "{$label}+{$value}";
                }

                $prefix = (int) ($effect['count'] ?? 0) > 0
                    ? "{$effect['count']}件"
                    : '未设置件数';

                return $parts === [] ? $prefix : "{$prefix}: ".implode(', ', $parts);
            })
            ->implode(' | ');
    }
}
