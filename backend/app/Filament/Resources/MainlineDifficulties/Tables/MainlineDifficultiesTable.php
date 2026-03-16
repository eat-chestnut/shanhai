<?php

namespace App\Filament\Resources\MainlineDifficulties\Tables;

use App\Models\MainlineNode;
use App\Models\MainlineChapter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MainlineDifficultiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('node_id')
            ->columns([
                TextColumn::make('difficulty_id')
                    ->label('难度ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('difficulty_name')
                    ->label('难度名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('difficulty_order')
                    ->label('难度排序')
                    ->sortable(),
                TextColumn::make('node_id')
                    ->label('节点ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('node.node_name')
                    ->label('节点名称')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('node.chapter.chapter_name')
                    ->label('所属章节')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('recommended_power')
                    ->label('建议战力')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('first_clear_reward_group_id')
                    ->label('首通奖励组')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('chapter_id')
                    ->label('所属章节')
                    ->options(fn (): array => MainlineChapter::query()
                        ->orderBy('sort_order')
                        ->pluck('chapter_name', 'chapter_id')
                        ->all())
                    ->query(function (Builder $query, array $data): Builder {
                        if (filled($data['value'])) {
                            return $query->whereHas('node', function (Builder $nodeQuery) use ($data) {
                                $nodeQuery->where('chapter_id', $data['value']);
                            });
                        }
                        return $query;
                    }),
                SelectFilter::make('node_id')
                    ->label('所属节点')
                    ->options(fn (): array => MainlineNode::query()
                        ->orderBy('node_id')
                        ->pluck('node_id', 'node_id')
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
}
