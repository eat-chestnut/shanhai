<?php

namespace App\Filament\Resources\ScriptureMonsters\Pages;

use App\Filament\Resources\ScriptureMonsters\ScriptureMonsterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScriptureMonster extends EditRecord
{
    protected static string $resource = ScriptureMonsterResource::class;

    public function getTitle(): string
    {
        return '编辑经卷怪物';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
