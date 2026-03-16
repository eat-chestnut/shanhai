<?php

namespace App\Filament\Resources\MainlineNodes\Pages;

use App\Filament\Resources\MainlineNodes\MainlineNodeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMainlineNodes extends ListRecords
{
    protected static string $resource = MainlineNodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新增节点'),
        ];
    }
}
