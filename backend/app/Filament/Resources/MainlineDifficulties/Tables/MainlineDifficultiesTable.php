<?php

namespace App\Filament\Resources\MainlineDifficulties\Tables;

use App\Models\MainlineNode;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MainlineDifficultiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('node_id')
            ->columns([
                TextColumn::make('difficulty_id')
                    ->label('difficulty_id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('node_id')
                    ->label('node_id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('node.node_name')
                    ->label('node_name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('recommended_power')
                    ->label('recommended_power')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('first_clear_reward_group_id')
                    ->label('first_clear_reward_group_id')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('node_id')
                    ->label('node_id')
                    ->options(fn (): array => MainlineNode::query()
                        ->orderBy('node_id')
                        ->pluck('node_id', 'node_id')
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
}
