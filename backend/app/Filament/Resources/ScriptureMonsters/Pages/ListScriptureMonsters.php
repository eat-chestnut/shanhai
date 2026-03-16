<?php

namespace App\Filament\Resources\ScriptureMonsters\Pages;

use App\Filament\Resources\ScriptureMonsters\ScriptureMonsterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScriptureMonsters extends ListRecords
{
    protected static string $resource = ScriptureMonsterResource::class;

    public function getTitle(): string
    {
        return '经卷怪物列表';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新增经卷怪物'),
        ];
    }
}
