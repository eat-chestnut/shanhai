<?php

namespace App\Filament\Resources\ChallengeConfigs\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Schema;

class ChallengeConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('基础配置')
                    ->columns(2)
                    ->schema([
                        TextInput::make('challenge_id')
                            ->label('challenge_id')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('challenge_name')
                            ->label('challenge_name')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('challenge_type')
                            ->label('challenge_type')
                            ->required()
                            ->maxLength(100)
                            ->default('tower'),
                        Select::make('cycle_type')
                            ->label('cycle_type')
                            ->required()
                            ->native(false)
                            ->options([
                                'weekly' => 'weekly',
                                'permanent' => 'permanent',
                            ])
                            ->default('weekly'),
                        TextInput::make('unlock_level')
                            ->label('unlock_level')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(60),
                        TextInput::make('sort')
                            ->label('sort')
                            ->required()
                            ->numeric()
                            ->default(10),
                        Toggle::make('is_open')
                            ->label('is_open')
                            ->default(true),
                        Textarea::make('challenge_desc')
                            ->label('challenge_desc')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                FormSection::make('奖励预览')
                    ->schema([
                        Repeater::make('reward_preview')
                            ->label('reward_preview')
                            ->defaultItems(0)
                            ->reorderable()
                            ->schema([
                                TextInput::make('item_id')
                                    ->label('item_id')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('count')
                                    ->label('count')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
                FormSection::make('层数配置')
                    ->schema([
                        Repeater::make('floors')
                            ->label('floors')
                            ->defaultItems(1)
                            ->reorderable()
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => (string) ($state['floor_name'] ?? $state['floor_id'] ?? null))
                            ->schema([
                                TextInput::make('floor_id')
                                    ->label('floor_id')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('floor')
                                    ->label('floor')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1),
                                TextInput::make('floor_name')
                                    ->label('floor_name')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('unlock_level')
                                    ->label('unlock_level')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(60),
                                TextInput::make('recommended_power')
                                    ->label('recommended_power')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                                TextInput::make('monster_group_id')
                                    ->label('monster_group_id')
                                    ->maxLength(100),
                                TagsInput::make('monster_ids')
                                    ->label('monster_ids')
                                    ->reorderable()
                                    ->columnSpanFull(),
                                TextInput::make('normal_reward_group_id')
                                    ->label('normal_reward_group_id')
                                    ->maxLength(100),
                                TextInput::make('first_clear_reward_group_id')
                                    ->label('first_clear_reward_group_id')
                                    ->maxLength(100),
                                TextInput::make('weekly_reward_group_id')
                                    ->label('weekly_reward_group_id')
                                    ->maxLength(100),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->helperText('可配置首通、周奖励与普通层数奖励，并直接挂怪物组与 monster_ids。'),
                    ]),
            ]);
    }
}
