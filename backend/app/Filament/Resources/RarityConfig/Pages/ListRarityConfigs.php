<?php

namespace App\Filament\Resources\RarityConfig\Pages;

use App\Filament\Resources\RarityConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRarityConfigs extends ListRecords
{
    protected static string $resource = RarityConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
