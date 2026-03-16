<?php

namespace App\Filament\Resources\DungeonDifficulties\Schemas;

use App\Models\Dungeon;
use App\Models\DungeonDifficulty;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class DungeonDifficultyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('副本难度信息')
                    ->columns(2)
                    ->schema([
                        Select::make('dungeon_id')
                            ->label('dungeon_id')
                            ->required()
                            ->live()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(fn (): array => Dungeon::query()
                                ->orderBy('dungeon_id')
                                ->get()
                                ->mapWithKeys(static fn (Dungeon $dungeon): array => [
                                    $dungeon->dungeon_id => "{$dungeon->dungeon_id} / {$dungeon->dungeon_name}",
                                ])
                                ->all()),
                        TextInput::make('difficulty_id')
                            ->label('difficulty_id')
                            ->required()
                            ->maxLength(100)
                            ->unique(
                                table: DungeonDifficulty::class,
                                column: 'difficulty_id',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule->where('dungeon_id', $get('dungeon_id')),
                            ),
                        TextInput::make('recommended_power')
                            ->label('recommended_power')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('first_clear_reward_group_id')
                            ->label('first_clear_reward_group_id')
                            ->maxLength(100)
                            ->placeholder('reward_dungeon_gem_easy')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
