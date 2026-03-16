<?php

namespace App\Filament\Resources\MonsterDrops\Pages;

use App\Filament\Resources\MonsterDrops\MonsterDropResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMonsterDrop extends EditRecord
{
    protected static string $resource = MonsterDropResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
