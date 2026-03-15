<?php

namespace App\Filament\Resources\CharacterClasses\Tables;

use App\Enums\RoleType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CharacterClassesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('class_id')
            ->columns([
                TextColumn::make('class_id')
                    ->label('class_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('class_name')
                    ->label('class_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('class_desc')
                    ->label('class_desc')
                    ->searchable()
                    ->limit(40)
                    ->wrap(),
                TextColumn::make('role_type')
                    ->label('role_type')
                    ->badge()
                    ->formatStateUsing(
                        static fn (string $state): string => RoleType::tryFrom($state)?->label() ?? $state,
                    )
                    ->sortable(),
                ToggleColumn::make('is_open')
                    ->label('is_open')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role_type')
                    ->label('role_type')
                    ->options(RoleType::options()),
                TernaryFilter::make('is_open')
                    ->label('is_open')
                    ->trueLabel('开放')
                    ->falseLabel('关闭'),
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
