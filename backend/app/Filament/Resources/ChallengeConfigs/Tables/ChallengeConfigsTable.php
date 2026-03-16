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
                    ->label('挑战ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('challenge_name')
                    ->label('挑战名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('challenge_type')
                    ->label('挑战类型')
                    ->badge()
                    ->formatStateUsing(static fn (string $state): string => match ($state) {
                        'tower' => '试炼塔',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('unlock_level')
                    ->label('解锁等级')
                    ->sortable(),
                TextColumn::make('cycle_type')
                    ->label('周期类型')
                    ->badge()
                    ->formatStateUsing(static fn (string $state): string => match ($state) {
                        'weekly' => '每周',
                        'permanent' => '常驻',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('floors')
                    ->label('楼层信息')
                    ->formatStateUsing(static fn (mixed $state): string => self::formatFloors($state))
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
