<?php

namespace App\Filament\Resources\MonsterDrops\Pages;

use App\Filament\Resources\MonsterDrops\MonsterDropResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMonsterDrops extends ListRecords
{
    protected static string $resource = MonsterDropResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
