<?php

namespace App\Filament\Resources\ScriptureChapterBindings\Pages;

use App\Filament\Resources\ScriptureChapterBindings\ScriptureChapterBindingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScriptureChapterBinding extends EditRecord
{
    protected static string $resource = ScriptureChapterBindingResource::class;

    public function getTitle(): string
    {
        return '编辑经卷章节绑定';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
