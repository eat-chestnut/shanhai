<?php

namespace App\Filament\Resources\DungeonDifficulties\Pages;

use App\Filament\Resources\DungeonDifficulties\DungeonDifficultyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDungeonDifficulties extends ListRecords
{
    protected static string $resource = DungeonDifficultyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
