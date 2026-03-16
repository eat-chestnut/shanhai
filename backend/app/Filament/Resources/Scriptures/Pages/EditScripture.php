<?php

namespace App\Filament\Resources\Scriptures\Pages;

use App\Filament\Resources\Scriptures\ScriptureResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScripture extends EditRecord
{
    protected static string $resource = ScriptureResource::class;

    public function getTitle(): string
    {
        return '编辑经卷';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
