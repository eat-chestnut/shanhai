<?php

namespace App\Filament\Resources\ScriptureWorldTiers\Pages;

use App\Filament\Resources\ScriptureWorldTiers\ScriptureWorldTierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScriptureWorldTiers extends ListRecords
{
    protected static string $resource = ScriptureWorldTierResource::class;

    public function getTitle(): string
    {
        return '经卷世界等级列表';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新增世界等级区间'),
        ];
    }
}
