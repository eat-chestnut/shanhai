<?php

namespace App\Filament\Resources\CharacterClasses\Pages;

use App\Filament\Resources\CharacterClasses\CharacterClassResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCharacterClasses extends ListRecords
{
    protected static string $resource = CharacterClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新增职业'),
        ];
    }
}
