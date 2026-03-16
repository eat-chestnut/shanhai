<?php

namespace App\Filament\Resources\ItemConfig\Pages;

use App\Filament\Resources\ItemConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItemConfigs extends ListRecords
{
    protected static string $resource = ItemConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('创建物品配置'),
        ];
    }
}
