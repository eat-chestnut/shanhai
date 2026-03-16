<?php

namespace App\Filament\Resources\MainlineChapters\Schemas;

use App\Models\MainlineChapter;
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
                            ->options(fn (): array => MainlineChapter::query()
                                ->where('chapter_id', '!=', request()->route('record')?->chapter_id)
                                ->orderBy('sort_order')
                                ->get()
                                ->mapWithKeys(static fn (MainlineChapter $chapter): array => [
                                    $chapter->chapter_id => "{$chapter->chapter_id} / {$chapter->chapter_name}",
                                ])
                                ->all()),
                        TextInput::make('required_previous_highest_difficulty')
                            ->label('前置章节最高难度要求')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('无要求')
                            ->helperText('需要前置章节通关的最高难度，留空表示不要求'),
                    ]),
            ]);
    }
}
