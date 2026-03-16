<?php

namespace App\Filament\Resources\ScriptureDropTags\Pages;

use App\Filament\Resources\ScriptureDropTags\ScriptureDropTagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScriptureDropTag extends EditRecord
{
    protected static string $resource = ScriptureDropTagResource::class;

    public function getTitle(): string
    {
        return '编辑掉落标签';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
