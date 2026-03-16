<?php

namespace App\Filament\Resources\ScriptureChapterBindings\Pages;

use App\Filament\Resources\ScriptureChapterBindings\ScriptureChapterBindingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateScriptureChapterBinding extends CreateRecord
{
    protected static string $resource = ScriptureChapterBindingResource::class;

    public function getTitle(): string
    {
        return '新增经卷章节绑定';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
