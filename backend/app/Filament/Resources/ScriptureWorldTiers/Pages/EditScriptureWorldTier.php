<?php

namespace App\Filament\Resources\ScriptureWorldTiers\Pages;

use App\Filament\Resources\ScriptureWorldTiers\ScriptureWorldTierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScriptureWorldTier extends EditRecord
{
    protected static string $resource = ScriptureWorldTierResource::class;

    public function getTitle(): string
    {
        return '编辑世界等级区间';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
