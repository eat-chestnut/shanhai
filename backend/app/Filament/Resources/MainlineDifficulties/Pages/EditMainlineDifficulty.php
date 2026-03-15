<?php

namespace App\Filament\Resources\MainlineDifficulties\Pages;

use App\Filament\Resources\MainlineDifficulties\MainlineDifficultyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMainlineDifficulty extends EditRecord
{
    protected static string $resource = MainlineDifficultyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
