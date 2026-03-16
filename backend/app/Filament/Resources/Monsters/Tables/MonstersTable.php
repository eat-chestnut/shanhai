<?php

namespace App\Filament\Resources\Monsters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MonstersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('monster_id')
            ->columns([
                TextColumn::make('monster_id')
                    ->label('怪物ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('怪物名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('combat_role')
                    ->label('战斗角色')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('base_hp')
                    ->label('基础生命值')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('base_atk')
                    ->label('基础攻击力')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_boss')
                    ->label('是否为Boss')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('behavior_profile.patterns')
                    ->label('技能模式')
                    ->formatStateUsing(static fn (mixed $state): string => is_array($state) ? (string) count($state).' 个模式' : '-')
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_boss')
                    ->label('怪物类型')
                    ->trueLabel('Boss')
                    ->falseLabel('普通怪'),
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
