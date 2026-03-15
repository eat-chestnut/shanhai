<?php

namespace App\Filament\Resources\MainlineNodes\Schemas;

use App\Models\MainlineChapter;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
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
                            ->label('node_id')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        Select::make('chapter_id')
                            ->label('chapter_id')
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
                            ->label('node_name')
                            ->required()
                            ->maxLength(100)
                            ->columnSpanFull(),
                    ]),
                FormSection::make('解锁与难度')
                    ->columns(2)
                    ->schema([
                        TextInput::make('unlock_condition.level')
                            ->label('unlock_condition.level')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        TagsInput::make('difficulty_ids')
                            ->label('difficulty_ids')
                            ->required()
                            ->reorderable()
                            ->nestedRecursiveRules(['distinct'])
                            ->helperText('通常由难度管理自动同步；也可在这里手动调整导出顺序。')
                            ->columnSpanFull(),
                        KeyValue::make('unlock_condition.conditions')
                            ->label('unlock_condition.conditions')
                            ->keyLabel('条件键')
                            ->valueLabel('条件值')
                            ->default([])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
