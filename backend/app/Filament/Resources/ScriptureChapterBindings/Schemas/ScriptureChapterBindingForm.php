<?php

namespace App\Filament\Resources\ScriptureChapterBindings\Schemas;

use App\Models\MainlineChapter;
use App\Models\Scripture;
use App\Models\ScriptureChapterBinding;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ScriptureChapterBindingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('绑定信息')
                    ->columns(2)
                    ->schema([
                        Select::make('scripture_id')
                            ->label('经卷')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(fn (): array => Scripture::query()
                                ->orderBy('sort_order')
                                ->orderBy('scripture_id')
                                ->get()
                                ->mapWithKeys(static fn (Scripture $scripture): array => [
                                    $scripture->scripture_id => "{$scripture->scripture_id} / {$scripture->scripture_name}",
                                ])
                                ->all()),
                        Select::make('chapter_id')
                            ->label('章节')
                            ->required()
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
                                ->all()),
                        TextInput::make('sort_order')
                            ->label('排序')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
