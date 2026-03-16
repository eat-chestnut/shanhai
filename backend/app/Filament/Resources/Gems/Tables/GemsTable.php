<?php

namespace App\Filament\Resources\Gems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('gem_id')
            ->columns([
                TextColumn::make('gem_id')
                    ->label('宝石ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('宝石名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('宝石类型')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bonus_atk')
                    ->label('攻击加成')
                    ->sortable(),
                TextColumn::make('bonus_boss_dmg')
                    ->label('Boss伤害加成')
                    ->sortable(),
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
