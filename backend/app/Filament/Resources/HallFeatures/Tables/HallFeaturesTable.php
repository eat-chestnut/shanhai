<?php

namespace App\Filament\Resources\HallFeatures\Tables;

use App\Models\HallFeature;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HallFeaturesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('feature_id')
            ->columns([
                TextColumn::make('feature_id')
                    ->label('feature_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('feature_name')
                    ->label('feature_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('feature_type')
                    ->label('feature_type')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unlock_condition')
                    ->label('unlock_condition')
                    ->formatStateUsing(
                        static fn (?array $state): string => self::formatUnlockCondition($state),
                    )
                    ->wrap(),
                TextColumn::make('jump_target')
                    ->label('jump_target')
                    ->formatStateUsing(
                        static fn (?array $state): string => self::formatJumpTarget($state),
                    )
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('feature_type')
                    ->label('feature_type')
                    ->options(fn (): array => self::featureTypeOptions()),
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
     * @param  array<string, mixed>|null  $state
     */
    private static function formatUnlockCondition(?array $state): string
    {
        if ($state === null || $state === []) {
            return '-';
        }

        $parts = ['level='.(string) data_get($state, 'level', '-')];
        $conditions = data_get($state, 'conditions');

        if (is_array($conditions) && $conditions !== []) {
            $parts[] = 'conditions='.json_encode($conditions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return implode('; ', $parts);
    }

    /**
     * @param  array<string, mixed>|null  $state
     */
    private static function formatJumpTarget(?array $state): string
    {
        if ($state === null || $state === []) {
            return '-';
        }

        $parts = ['page='.(string) data_get($state, 'page', '-')];
        $params = data_get($state, 'params');

        if (is_array($params) && $params !== []) {
            $parts[] = 'params='.json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return implode('; ', $parts);
    }

    /**
     * @return array<string, string>
     */
    private static function featureTypeOptions(): array
    {
        return HallFeature::query()
            ->select('feature_type')
            ->distinct()
            ->orderBy('feature_type')
            ->pluck('feature_type', 'feature_type')
            ->all();
    }
}
