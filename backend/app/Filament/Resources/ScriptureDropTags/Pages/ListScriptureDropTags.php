<?php

namespace App\Filament\Resources\ScriptureDropTags\Pages;

use App\Filament\Resources\ScriptureDropTags\ScriptureDropTagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScriptureDropTags extends ListRecords
{
    protected static string $resource = ScriptureDropTagResource::class;

    public function getTitle(): string
    {
        return '经卷掉落标签列表';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新增掉落标签'),
        ];
    }
}
