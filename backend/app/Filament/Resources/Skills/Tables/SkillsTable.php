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
                    ->label('skill_id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('skill_name')
                    ->label('skill_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('class_id')
                    ->label('class_id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('characterClass.class_name')
                    ->label('class_name')
                    ->toggleable(),
                TextColumn::make('type')
                    ->label('type')
                    ->badge()
                    ->formatStateUsing(static fn (string $state): string => SkillType::tryFrom($state)?->label() ?? $state)
                    ->sortable(),
                TextColumn::make('effect_type')
                    ->label('effect_type')
                    ->badge()
                    ->formatStateUsing(static fn (?string $state): string => SkillEffectType::tryFrom((string) $state)?->label() ?? (string) $state)
                    ->sortable(),
                TextColumn::make('cooldown')
                    ->label('cooldown')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cost')
                    ->label('cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unlock_level')
                    ->label('unlock_level')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_open')
                    ->label('is_open')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('class_id')
                    ->label('class_id')
                    ->options(fn (): array => CharacterClass::query()->orderBy('class_id')->pluck('class_id', 'class_id')->all()),
                SelectFilter::make('type')
                    ->label('type')
                    ->options(SkillType::options()),
                TernaryFilter::make('is_open')
                    ->label('is_open'),
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
