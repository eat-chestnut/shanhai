<?php

namespace App\Filament\Resources\MainlineDifficulties\Pages;

use App\Filament\Resources\MainlineDifficulties\MainlineDifficultyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMainlineDifficulty extends CreateRecord
{
    protected static string $resource = MainlineDifficultyResource::class;

    public function getTitle(): string
    {
        return '创建主线难度';
    }
}
