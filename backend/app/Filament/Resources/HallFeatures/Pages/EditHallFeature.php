<?php

namespace App\Filament\Resources\HallFeatures\Pages;

use App\Filament\Resources\HallFeatures\HallFeatureResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHallFeature extends EditRecord
{
    protected static string $resource = HallFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
