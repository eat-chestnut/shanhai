<?php

namespace App\Filament\Resources\DungeonDifficulties\Pages;

use App\Filament\Resources\DungeonDifficulties\DungeonDifficultyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDungeonDifficulty extends EditRecord
{
    protected static string $resource = DungeonDifficultyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
