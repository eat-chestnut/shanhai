<?php

namespace App\Filament\Resources\ScriptureMonsters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScriptureMonstersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('monster_type')
            ->columns([
                TextColumn::make('monster_id')->label('怪物ID')->searchable()->sortable()->copyable(),
                TextColumn::make('name')->label('怪物名称')->searchable()->sortable(),
                TextColumn::make('monster_type')->label('怪物类型')->sortable(),
                TextColumn::make('race')->label('种族')->sortable(),
                TextColumn::make('rarity')->label('稀有度')->sortable(),
                TextColumn::make('base_hp')->label('生命值')->sortable(),
                TextColumn::make('base_atk')->label('攻击力')->sortable(),
                TextColumn::make('base_def')->label('防御力')->sortable(),
                TextColumn::make('move_speed')->label('移动速度')->sortable(),
                TextColumn::make('ai_type')->label('AI类型')->toggleable(),
                TextColumn::make('skill_ids')
                    ->label('技能ID')
                    ->formatStateUsing(static fn (mixed $state): string => is_array($state) ? implode('，', $state) : '-')
                    ->toggleable(),
                IconColumn::make('is_boss')->label('Boss')->boolean(),
                IconColumn::make('is_elite')->label('精英')->boolean(),
                IconColumn::make('is_enabled')->label('启用')->boolean(),
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
