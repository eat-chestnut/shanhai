<?php

namespace App\Filament\Resources\Dungeons\Pages;

use App\Filament\Resources\Dungeons\DungeonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDungeon extends EditRecord
{
    protected static string $resource = DungeonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
