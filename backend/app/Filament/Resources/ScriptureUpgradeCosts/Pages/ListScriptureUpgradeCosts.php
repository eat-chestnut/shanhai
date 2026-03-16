<?php

namespace App\Filament\Resources\ScriptureUpgradeCosts\Pages;

use App\Filament\Resources\ScriptureUpgradeCosts\ScriptureUpgradeCostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScriptureUpgradeCosts extends ListRecords
{
    protected static string $resource = ScriptureUpgradeCostResource::class;

    public function getTitle(): string
    {
        return '经卷升级成本列表';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新增升级成本'),
        ];
    }
}
