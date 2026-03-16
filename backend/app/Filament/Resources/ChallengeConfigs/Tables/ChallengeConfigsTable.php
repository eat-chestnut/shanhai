<?php

namespace App\Filament\Resources\ChallengeConfigs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChallengeConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('challenge_id')
                    ->label('challenge_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('challenge_name')
                    ->label('challenge_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('challenge_type')
                    ->label('challenge_type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('unlock_level')
                    ->label('unlock_level')
                    ->sortable(),
                TextColumn::make('cycle_type')
                    ->label('cycle_type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('floors')
                    ->label('floors')
                    ->formatStateUsing(static fn (mixed $state): string => self::formatFloors($state))
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

    private static function formatFloors(mixed $state): string
    {
        if (! is_array($state) || $state === []) {
            return '-';
        }

        $count = count($state);
        $lastFloor = collect($state)->sortBy('floor')->last();
        $lastFloorName = is_array($lastFloor) ? (string) ($lastFloor['floor_name'] ?? $lastFloor['floor_id'] ?? '') : '';

        return trim(sprintf('%d 层 / 终点 %s', $count, $lastFloorName), ' /');
    }
}
