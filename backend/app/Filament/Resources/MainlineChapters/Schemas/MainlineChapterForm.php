<?php

namespace App\Filament\Resources\MainlineChapters\Schemas;

use App\Models\MainlineChapter;
use App\Models\MainlineDifficulty;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Schema;

class MainlineChapterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('章节信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('chapter_id')
                            ->label('章节ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('chapter_name')
                            ->label('章节名称')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('unlock_level')
                            ->label('开启等级')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('sort_order')
                            ->label('排序')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->helperText('数值越小排序越靠前'),
                        Select::make('required_previous_chapter')
                            ->label('前置章节')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('无前置章节')
                            ->options(function (): array {
                                $record = request()->route('record');
                                $currentChapterId = $record instanceof MainlineChapter ? $record->chapter_id : (is_string($record) ? $record : null);

                                return MainlineChapter::query()
                                    ->when(
                                        filled($currentChapterId),
                                        static fn ($query) => $query->where('chapter_id', '!=', $currentChapterId),
                                    )
                                    ->orderBy('sort_order')
                                    ->get()
                                    ->mapWithKeys(static fn (MainlineChapter $chapter): array => [
                                        $chapter->chapter_id => "{$chapter->chapter_id} / {$chapter->chapter_name}",
                                    ])
                                    ->all();
                            }),
                        Select::make('required_previous_highest_difficulty')
                            ->label('前置章节最高难度要求')
                            ->native(false)
                            ->options([
                                'easy' => MainlineDifficulty::defaultDifficultyName('easy'),
                                'normal' => MainlineDifficulty::defaultDifficultyName('normal'),
                                'hard' => MainlineDifficulty::defaultDifficultyName('hard'),
                                'nightmare' => MainlineDifficulty::defaultDifficultyName('nightmare'),
                                'epic' => MainlineDifficulty::defaultDifficultyName('epic'),
                            ])
                            ->placeholder('无要求')
                            ->helperText('下一章节需要上一章节最终节点以该难度完成首通。'),
                    ]),
            ]);
    }
}
