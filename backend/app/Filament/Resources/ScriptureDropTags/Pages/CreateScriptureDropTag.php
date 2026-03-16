<?php

namespace App\Filament\Resources\ScriptureDropTags\Pages;

use App\Filament\Resources\ScriptureDropTags\ScriptureDropTagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateScriptureDropTag extends CreateRecord
{
    protected static string $resource = ScriptureDropTagResource::class;

    public function getTitle(): string
    {
        return '新增掉落标签';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
