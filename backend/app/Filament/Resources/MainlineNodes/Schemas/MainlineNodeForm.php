<?php

namespace App\Filament\Resources\MainlineNodes\Schemas;

use App\Models\MainlineChapter;
use App\Models\MainlineNode;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Schema;

class MainlineNodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('节点信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('node_id')
                            ->label('节点ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        Select::make('chapter_id')
                            ->label('所属章节')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(fn (): array => MainlineChapter::query()
                                ->orderBy('chapter_id')
                                ->get()
                                ->mapWithKeys(static fn (MainlineChapter $chapter): array => [
                                    $chapter->chapter_id => "{$chapter->chapter_id} / {$chapter->chapter_name}",
                                ])
                                ->all()),
                        TextInput::make('node_name')
                            ->label('节点名称')
                            ->required()
                            ->maxLength(100)
                            ->columnSpanFull(),
                    ]),
                FormSection::make('解锁与难度')
                    ->columns(2)
                    ->schema([
                        TextInput::make('unlock_condition.level')
                            ->label('等级要求')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        Select::make('unlock_condition.clear_node_id')
                            ->label('前置节点')
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
                        TagsInput::make('difficulty_ids')
                            ->label('难度列表')
                            ->required()
                            ->reorderable()
                            ->nestedRecursiveRules(['distinct'])
                            ->helperText('通常由难度管理自动同步；也可在这里手动调整导出顺序。')
                            ->columnSpanFull(),
                        KeyValue::make('unlock_condition.conditions')
                            ->label('额外条件')
                            ->keyLabel('条件键')
                            ->valueLabel('条件值')
                            ->default([])
                            ->helperText('可额外补充运营条件；clear_node_id 建议直接用上方关联选择。')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
