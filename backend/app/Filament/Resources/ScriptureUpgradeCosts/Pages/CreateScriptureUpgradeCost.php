<?php

namespace App\Filament\Resources\ScriptureUpgradeCosts\Pages;

use App\Filament\Resources\ScriptureUpgradeCosts\ScriptureUpgradeCostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateScriptureUpgradeCost extends CreateRecord
{
    protected static string $resource = ScriptureUpgradeCostResource::class;

    public function getTitle(): string
    {
        return '新增升级成本';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
