<?php

namespace App\Filament\Resources\ChallengeConfigs\Schemas;

use App\Models\Item;
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
                            ->label('挑战ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('challenge_name')
                            ->label('挑战名称')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('challenge_type')
                            ->label('挑战类型')
                            ->required()
                            ->maxLength(100)
                            ->default('tower'),
                        Select::make('cycle_type')
                            ->label('周期类型')
                            ->required()
                            ->native(false)
                            ->options([
                                'weekly' => '每周',
                                'permanent' => '常驻',
                            ])
                            ->default('weekly'),
                        TextInput::make('unlock_level')
                            ->label('解锁等级')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(60),
                        TextInput::make('sort')
                            ->label('排序')
                            ->required()
                            ->numeric()
                            ->default(10),
                        Toggle::make('is_open')
                            ->label('是否开启')
                            ->default(true),
                        Textarea::make('challenge_desc')
                            ->label('挑战说明')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                FormSection::make('奖励预览')
                    ->schema([
                        Repeater::make('reward_preview')
                            ->label('奖励预览')
                            ->defaultItems(0)
                            ->reorderable()
                            ->schema([
                                Select::make('item_id')
                                    ->label('物品')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->options(fn (): array => Item::getEnabledItemOptions()),
                                TextInput::make('count')
                                    ->label('数量')
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
                            ->label('楼层配置')
                            ->defaultItems(1)
                            ->reorderable()
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => (string) ($state['floor_name'] ?? $state['floor_id'] ?? null))
                            ->schema([
                                TextInput::make('floor_id')
                                    ->label('楼层ID')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('floor')
                                    ->label('层数')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1),
                                TextInput::make('floor_name')
                                    ->label('楼层名称')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('unlock_level')
                                    ->label('解锁等级')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(60),
                                TextInput::make('recommended_power')
                                    ->label('建议战力')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                                TextInput::make('monster_group_id')
                                    ->label('怪物组ID')
                                    ->maxLength(100),
                                TagsInput::make('monster_ids')
                                    ->label('怪物ID列表')
                                    ->reorderable()
                                    ->columnSpanFull(),
                                TextInput::make('normal_reward_group_id')
                                    ->label('常规奖励组ID')
                                    ->maxLength(100),
                                TextInput::make('first_clear_reward_group_id')
                                    ->label('首通奖励组ID')
                                    ->maxLength(100),
                                TextInput::make('weekly_reward_group_id')
                                    ->label('周奖励组ID')
                                    ->maxLength(100),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->helperText('可配置首通、周奖励与普通层数奖励，并直接挂怪物组与 monster_ids。'),
                    ]),
            ]);
    }
}
