<?php

namespace App\Filament\Resources\Equipment\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EquipmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('equip_id')
            ->columns([
                TextColumn::make('equip_id')
                    ->label('装备ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('装备名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('装备类型')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('level')
                    ->label('等级')
                    ->sortable(),
                TextColumn::make('base_atk')
                    ->label('基础攻击')
                    ->sortable(),
                TextColumn::make('base_def')
                    ->label('基础防御')
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
