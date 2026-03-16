<?php

namespace App\Filament\Resources\BlueAffixes\Pages;

use App\Filament\Resources\BlueAffixes\BlueAffixResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBlueAffixes extends ListRecords
{
    protected static string $resource = BlueAffixResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新增蓝词条'),
        ];
    }
}
