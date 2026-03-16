<?php

namespace App\Filament\Resources\RarityConfigs\Pages;

use App\Filament\Resources\RarityConfigs\RarityConfigResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRarityConfig extends CreateRecord
{
    protected static string $resource = RarityConfigResource::class;

    public function getTitle(): string
    {
        return '创建稀有度';
    }
}
