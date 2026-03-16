<?php

namespace App\Filament\Resources\Dungeons\Schemas;

use App\Models\MainlineNode;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Schema;

class DungeonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('副本信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('dungeon_id')
                            ->label('副本ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('dungeon_name')
                            ->label('副本名称')
                            ->required()
                            ->maxLength(100),
                        Textarea::make('dungeon_desc')
                            ->label('副本描述')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('unlock_level')
                            ->label('开启等级')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('daily_limit')
                            ->label('每日限制次数')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(3),
                        Select::make('unlock_stage_node_id')
                            ->label('解锁关卡节点ID')
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
                        TagsInput::make('main_rewards')
                            ->label('主要奖励')
                            ->reorderable()
                            ->helperText('填写主要产出 item_id，客户端详情和运营校验会直接展示。')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
