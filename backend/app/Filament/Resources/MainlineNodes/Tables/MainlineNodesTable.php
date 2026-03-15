<?php

namespace App\Filament\Resources\MainlineNodes\Tables;

use App\Models\MainlineChapter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MainlineNodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('node_id')
            ->columns([
                TextColumn::make('node_id')
                    ->label('node_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('chapter_id')
                    ->label('chapter_id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('chapter.chapter_name')
                    ->label('chapter_name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('node_name')
                    ->label('node_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unlock_condition')
                    ->label('unlock_condition')
                    ->formatStateUsing(static fn (?array $state): string => self::formatUnlockCondition($state))
                    ->wrap(),
                TextColumn::make('difficulty_ids')
                    ->label('difficulty_ids')
                    ->formatStateUsing(static fn (?array $state): string => implode(', ', $state ?? []))
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('chapter_id')
                    ->label('chapter_id')
                    ->options(fn (): array => MainlineChapter::query()
                        ->orderBy('chapter_id')
                        ->pluck('chapter_id', 'chapter_id')
                        ->all()),
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

    /**
     * @param  array<string, mixed>|null  $state
     */
    private static function formatUnlockCondition(?array $state): string
    {
        if ($state === null || $state === []) {
            return '-';
        }

        $parts = ['level='.(string) data_get($state, 'level', '-')];
        $conditions = data_get($state, 'conditions');

        if (is_array($conditions) && $conditions !== []) {
            $parts[] = 'conditions='.json_encode($conditions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return implode('; ', $parts);
    }
}
