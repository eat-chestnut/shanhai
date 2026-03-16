<?php

namespace App\Filament\Resources\ChallengeConfigs\Pages;

use App\Filament\Resources\ChallengeConfigs\ChallengeConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChallengeConfigs extends ListRecords
{
    protected static string $resource = ChallengeConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新增挑战'),
        ];
    }
}
