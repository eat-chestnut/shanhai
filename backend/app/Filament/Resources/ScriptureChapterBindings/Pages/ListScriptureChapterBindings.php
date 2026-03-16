<?php

namespace App\Filament\Resources\ScriptureChapterBindings\Pages;

use App\Filament\Resources\ScriptureChapterBindings\ScriptureChapterBindingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScriptureChapterBindings extends ListRecords
{
    protected static string $resource = ScriptureChapterBindingResource::class;

    public function getTitle(): string
    {
        return '经卷章节绑定列表';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新增绑定'),
        ];
    }
}
