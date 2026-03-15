<?php

namespace App\Filament\Resources\PurpleRefinements\Pages;

use App\Filament\Resources\PurpleRefinements\PurpleRefinementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPurpleRefinements extends ListRecords
{
    protected static string $resource = PurpleRefinementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
