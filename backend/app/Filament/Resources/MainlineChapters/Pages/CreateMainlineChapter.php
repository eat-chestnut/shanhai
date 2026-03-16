<?php

namespace App\Filament\Resources\MainlineChapters\Pages;

use App\Filament\Resources\MainlineChapters\MainlineChapterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMainlineChapter extends CreateRecord
{
    protected static string $resource = MainlineChapterResource::class;

    public function getTitle(): string
    {
        return '新增章节';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
