<?php

namespace App\Filament\Resources\Scriptures\Pages;

use App\Filament\Resources\Scriptures\ScriptureResource;
use Filament\Resources\Pages\CreateRecord;

class CreateScripture extends CreateRecord
{
    protected static string $resource = ScriptureResource::class;

    public function getTitle(): string
    {
        return '新增经卷';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
