<?php

namespace App\Filament\Resources\ScriptureWorldTiers\Pages;

use App\Filament\Resources\ScriptureWorldTiers\ScriptureWorldTierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateScriptureWorldTier extends CreateRecord
{
    protected static string $resource = ScriptureWorldTierResource::class;

    public function getTitle(): string
    {
        return '新增世界等级区间';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
