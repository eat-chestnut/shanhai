<?php

namespace App\Filament\Resources\Monsters\Pages;

use App\Filament\Resources\Monsters\MonsterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMonster extends EditRecord
{
    protected static string $resource = MonsterResource::class;

    public function getTitle(): string
    {
        return '编辑怪物';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
