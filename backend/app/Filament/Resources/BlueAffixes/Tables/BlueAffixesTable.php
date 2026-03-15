<?php

namespace App\Filament\Resources\BlueAffixes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BlueAffixesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('affix_id')
            ->columns([
                TextColumn::make('affix_id')
                    ->label('affix_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bonuses')
                    ->label('bonuses')
                    ->formatStateUsing(
                        static fn (?array $state): string => json_encode($state ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    )
                    ->wrap(),
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
