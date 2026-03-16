<?php

namespace App\Filament\Resources\Skills\Tables;

use App\Enums\SkillEffectType;
use App\Enums\SkillType;
use App\Models\CharacterClass;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SkillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('class_id')
            ->columns([
                TextColumn::make('skill_id')
                    ->label('技能ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('skill_name')
                    ->label('技能名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('class_id')
                    ->label('职业ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('characterClass.class_name')
                    ->label('职业名称')
                    ->toggleable(),
                TextColumn::make('type')
                    ->label('技能类型')
                    ->badge()
                    ->formatStateUsing(static fn (string $state): string => SkillType::tryFrom($state)?->label() ?? $state)
                    ->sortable(),
                TextColumn::make('effect_type')
                    ->label('效果类型')
                    ->badge()
                    ->formatStateUsing(static fn (?string $state): string => SkillEffectType::tryFrom((string) $state)?->label() ?? (string) $state)
                    ->sortable(),
                TextColumn::make('cooldown')
                    ->label('冷却时间')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cost')
                    ->label('消耗')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unlock_level')
                    ->label('解锁等级')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_open')
                    ->label('是否开放')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('class_id')
                    ->label('职业ID')
                    ->options(fn (): array => CharacterClass::query()->orderBy('class_id')->pluck('class_id', 'class_id')->all()),
                SelectFilter::make('type')
                    ->label('技能类型')
                    ->options(SkillType::options()),
                TernaryFilter::make('is_open')
                    ->label('是否开放'),
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
