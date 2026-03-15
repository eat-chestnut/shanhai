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
                    ->label('monster_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('base_hp')
                    ->label('base_hp')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('base_atk')
                    ->label('base_atk')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_boss')
                    ->label('is_boss')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_boss')
                    ->label('is_boss')
                    ->trueLabel('Boss')
                    ->falseLabel('普通怪'),
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
