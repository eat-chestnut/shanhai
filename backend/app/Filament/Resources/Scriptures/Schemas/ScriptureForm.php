<?php

namespace App\Filament\Resources\Scriptures\Schemas;

use App\Models\MainlineChapter;
use App\Models\Scripture;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ScriptureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('经卷基础信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('scripture_id')
                            ->label('经卷ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(Scripture::class, 'scripture_id', ignoreRecord: true)
                            ->helperText('正式规格中的经卷唯一标识。'),
                        TextInput::make('scripture_name')
                            ->label('经卷名称')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('scripture_group')
                            ->label('经卷分组')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('sort_order')
                            ->label('排序')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_enabled')
                            ->label('是否启用')
                            ->default(true)
                            ->inline(false),
                    ]),
                Section::make('解锁条件')
                    ->columns(2)
                    ->schema([
                        Select::make('unlock_condition.clear_chapter_id')
                            ->label('通关章节条件')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(fn (): array => MainlineChapter::query()
                                ->orderBy('sort_order')
                                ->orderBy('chapter_id')
                                ->get()
                                ->mapWithKeys(static fn (MainlineChapter $chapter): array => [
                                    $chapter->chapter_id => "{$chapter->chapter_id} / {$chapter->chapter_name}",
                                ])
                                ->all())
                            ->helperText('严格按正式 JSON 中的 clear_chapter_id 配置。'),
                        TextInput::make('unlock_condition.player_level')
                            ->label('玩家等级条件')
                            ->required()
                            ->numeric()
                            ->default(20)
                            ->helperText('严格按正式 JSON 中的 player_level 配置。'),
                    ]),
            ]);
    }
}
