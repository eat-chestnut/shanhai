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
                    ->label('gem_id')
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
                TextColumn::make('bonus_atk')
                    ->label('bonus_atk')
                    ->sortable(),
                TextColumn::make('bonus_boss_dmg')
                    ->label('bonus_boss_dmg')
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
