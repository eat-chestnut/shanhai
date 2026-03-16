<?php

namespace App\Filament\Resources\RarityConfigs\Pages;

use App\Filament\Resources\RarityConfigs\RarityConfigResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListRarityConfigs extends ListRecords
{
    protected static string $resource = RarityConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('创建稀有度'),
        ];
    }
}
