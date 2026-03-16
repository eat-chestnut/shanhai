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
                    ->label('节点ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('chapter_id')
                    ->label('所属章节ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('chapter.chapter_name')
                    ->label('章节名称')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('node_name')
                    ->label('节点名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unlock_condition')
                    ->label('解锁条件')
                    ->formatStateUsing(static fn (mixed $state): string => self::formatUnlockCondition($state))
                    ->wrap(),
                TextColumn::make('difficulty_ids')
                    ->label('难度列表')
                    ->formatStateUsing(static fn (mixed $state): string => self::formatDifficultyIds($state))
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('chapter_id')
                    ->label('所属章节')
                    ->options(fn (): array => MainlineChapter::query()
                        ->orderBy('chapter_id')
                        ->pluck('chapter_id', 'chapter_id')
                        ->all()),
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

    private static function formatUnlockCondition(mixed $state): string
    {
        if (! is_array($state)) {
            return filled($state) ? (string) $state : '-';
        }

        if ($state === null || $state === []) {
            return '-';
        }

        $parts = ['等级要求='.(string) data_get($state, 'level', '-')];
        $clearNodeId = (string) data_get($state, 'clear_node_id', '');

        if ($clearNodeId !== '') {
            $parts[] = '前置节点='.$clearNodeId;
        }
        $conditions = data_get($state, 'conditions');

        if (is_array($conditions) && $conditions !== []) {
            $parts[] = '额外条件='.json_encode($conditions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return implode('; ', $parts);
    }

    private static function formatDifficultyIds(mixed $state): string
    {
        if (is_array($state)) {
            return implode(', ', $state);
        }

        return filled($state) ? (string) $state : '-';
    }
}
