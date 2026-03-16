<?php

namespace App\Filament\Resources\Scriptures\Pages;

use App\Filament\Resources\Scriptures\ScriptureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScriptures extends ListRecords
{
    protected static string $resource = ScriptureResource::class;

    public function getTitle(): string
    {
        return '经卷列表';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新增经卷'),
        ];
    }
}
