<?php

namespace App\Filament\Resources\MainlineDifficulties\Pages;

use App\Filament\Resources\MainlineDifficulties\MainlineDifficultyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMainlineDifficulties extends ListRecords
{
    protected static string $resource = MainlineDifficultyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新增难度'),
        ];
    }
}
