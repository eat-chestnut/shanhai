<?php

namespace App\Filament\Resources\HallFeatures\Pages;

use App\Filament\Resources\HallFeatures\HallFeatureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHallFeatures extends ListRecords
{
    protected static string $resource = HallFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
