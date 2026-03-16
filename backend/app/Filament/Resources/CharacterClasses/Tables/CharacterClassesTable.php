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
                    ->label('职业ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('class_name')
                    ->label('职业名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('class_desc')
                    ->label('职业描述')
                    ->searchable()
                    ->limit(40)
                    ->wrap(),
                TextColumn::make('role_type')
                    ->label('角色定位')
                    ->badge()
                    ->formatStateUsing(
                        static fn (string $state): string => RoleType::tryFrom($state)?->label() ?? $state,
                    )
                    ->sortable(),
                ToggleColumn::make('is_open')
                    ->label('是否开放')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role_type')
                    ->label('角色定位')
                    ->options(RoleType::options()),
                TernaryFilter::make('is_open')
                    ->label('是否开放')
                    ->trueLabel('开放')
                    ->falseLabel('关闭'),
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
