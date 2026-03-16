<?php

namespace App\Filament\Resources\MainlineDifficulties\Schemas;

use App\Models\MainlineDifficulty;
use App\Models\MainlineNode;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class MainlineDifficultyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('难度信息')
                    ->columns(2)
                    ->schema([
                        Select::make('node_id')
                            ->label('所属节点')
                            ->required()
                            ->live()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(fn (): array => MainlineNode::query()
                                ->orderBy('node_id')
                                ->get()
                                ->mapWithKeys(static fn (MainlineNode $node): array => [
                                    $node->node_id => "{$node->node_id} / {$node->node_name}",
                                ])
                                ->all()),
                        TextInput::make('difficulty_id')
                            ->label('难度ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(
                                table: MainlineDifficulty::class,
                                column: 'difficulty_id',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule->where('node_id', $get('node_id')),
                            ),
                        TextInput::make('difficulty_order')
                            ->label('难度排序')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('difficulty_name')
                            ->label('难度名称')
                            ->required()
                            ->maxLength(100)
                            ->helperText('用于客户端与后台展示，例如：简单、普通、困难、梦魇。'),
                        TextInput::make('recommended_power')
                            ->label('建议战力')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('first_clear_reward_group_id')
                            ->label('首通奖励组ID')
                            ->maxLength(100)
                            ->helperText('填写首通奖励组 ID，用于挂载首通奖励。'),
                    ]),
            ]);
    }
}
