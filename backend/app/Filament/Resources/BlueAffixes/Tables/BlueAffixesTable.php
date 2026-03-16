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
                    ->label('蓝词条ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('蓝词条名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bonuses')
                    ->label('属性加成')
                    ->formatStateUsing(
                        static fn (mixed $state): string => self::formatBonuses($state),
                    )
                    ->wrap(),
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

    private static function formatBonuses(mixed $state): string
    {
        if (is_array($state)) {
            return (string) json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return filled($state) ? (string) $state : '-';
    }
}
