<?php

namespace App\Filament\Resources\ScriptureMonsters\Pages;

use App\Filament\Resources\ScriptureMonsters\ScriptureMonsterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateScriptureMonster extends CreateRecord
{
    protected static string $resource = ScriptureMonsterResource::class;

    public function getTitle(): string
    {
        return '新增经卷怪物';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
