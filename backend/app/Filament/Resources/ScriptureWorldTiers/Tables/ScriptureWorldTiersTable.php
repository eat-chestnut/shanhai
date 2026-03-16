<?php

namespace App\Filament\Resources\ScriptureWorldTiers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScriptureWorldTiersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('world_level_start')
            ->columns([
                TextColumn::make('scripture_id')
                    ->label('经卷ID')
                    ->sortable(),
                TextColumn::make('world_level_start')
                    ->label('起点')
                    ->sortable(),
                TextColumn::make('world_level_end')
                    ->label('终点')
                    ->sortable(),
                TextColumn::make('hp_scale')->label('生命倍率'),
                TextColumn::make('atk_scale')->label('攻击倍率'),
                TextColumn::make('def_scale')->label('防御倍率'),
                TextColumn::make('reward_scale')->label('掉落倍率'),
                TextColumn::make('gold_scale')->label('金币倍率'),
                TextColumn::make('normal_monster_ids')
                    ->label('普通怪池')
                    ->formatStateUsing(static fn (mixed $state): string => is_array($state) ? implode('，', $state) : '-')
                    ->toggleable(),
                TextColumn::make('elite_monster_ids')
                    ->label('精英怪池')
                    ->formatStateUsing(static fn (mixed $state): string => is_array($state) ? implode('，', $state) : '-')
                    ->toggleable(),
                TextColumn::make('boss_monster_ids')
                    ->label('Boss池')
                    ->formatStateUsing(static fn (mixed $state): string => is_array($state) ? implode('，', $state) : '-')
                    ->toggleable(),
                TextColumn::make('extra_drop_tags')
                    ->label('掉落标签')
                    ->formatStateUsing(static fn (mixed $state): string => is_array($state) ? implode('，', $state) : '-')
                    ->toggleable(),
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
