<?php

namespace App\Filament\Resources\ScriptureUpgradeCosts\Pages;

use App\Filament\Resources\ScriptureUpgradeCosts\ScriptureUpgradeCostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScriptureUpgradeCost extends EditRecord
{
    protected static string $resource = ScriptureUpgradeCostResource::class;

    public function getTitle(): string
    {
        return '编辑升级成本';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
