<?php

namespace App\Filament\Resources\Dungeons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DungeonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('dungeon_id')
            ->columns([
                TextColumn::make('dungeon_id')
                    ->label('副本ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('dungeon_name')
                    ->label('副本名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dungeon_desc')
                    ->label('副本描述')
                    ->limit(32)
                    ->toggleable(),
                TextColumn::make('unlock_level')
                    ->label('开启等级')
                    ->sortable(),
                TextColumn::make('daily_limit')
                    ->label('每日限制')
                    ->sortable(),
                TextColumn::make('main_rewards')
                    ->label('主要奖励')
                    ->formatStateUsing(static fn (mixed $state): string => is_array($state) ? implode(', ', $state) : (string) $state)
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('unlock_stage_node_id')
                    ->label('解锁节点')
                    ->toggleable(),
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
}
