<?php

namespace App\Filament\Resources\MainlineChapters\Pages;

use App\Filament\Resources\MainlineChapters\MainlineChapterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMainlineChapter extends EditRecord
{
    protected static string $resource = MainlineChapterResource::class;

    public function getTitle(): string
    {
        return '编辑章节';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
