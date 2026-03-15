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
                    ->label('equip_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('level')
                    ->label('level')
                    ->sortable(),
                TextColumn::make('base_atk')
                    ->label('base_atk')
                    ->sortable(),
                TextColumn::make('base_def')
                    ->label('base_def')
                    ->sortable(),
            ])
            ->filters([
                //
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
}
