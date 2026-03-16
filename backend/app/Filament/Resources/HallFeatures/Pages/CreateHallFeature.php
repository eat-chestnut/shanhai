<?php

namespace App\Filament\Resources\HallFeatures\Pages;

use App\Filament\Resources\HallFeatures\HallFeatureResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHallFeature extends CreateRecord
{
    protected static string $resource = HallFeatureResource::class;

    public function getTitle(): string
    {
        return '新增大厅功能';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
