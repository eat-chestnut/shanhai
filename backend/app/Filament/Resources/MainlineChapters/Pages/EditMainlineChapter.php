<?php

namespace App\Filament\Resources\MainlineChapters\Pages;

use App\Filament\Resources\MainlineChapters\MainlineChapterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMainlineChapter extends EditRecord
{
    protected static string $resource = MainlineChapterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
