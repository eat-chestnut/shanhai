<?php

namespace App\Filament\Resources\ScriptureDropTags\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScriptureDropTagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('drop_tag')
            ->columns([
                TextColumn::make('drop_tag')
                    ->label('掉落标签')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('tag_name')
                    ->label('标签名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('items')
                    ->label('物品组')
                    ->formatStateUsing(static function (mixed $state): string {
                        if (! is_array($state) || $state === []) {
                            return '无';
                        }

                        return collect($state)
                            ->map(static fn (array $entry): string => sprintf(
                                '%s[%d-%d, 权重%d]',
                                (string) ($entry['item_id'] ?? ''),
                                (int) ($entry['min'] ?? 0),
                                (int) ($entry['max'] ?? 0),
                                (int) ($entry['weight'] ?? 0),
                            ))
                            ->implode('；');
                    })
                    ->wrap(),
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
