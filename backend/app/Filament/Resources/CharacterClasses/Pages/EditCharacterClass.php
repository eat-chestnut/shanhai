<?php

namespace App\Filament\Resources\CharacterClasses\Pages;

use App\Filament\Resources\CharacterClasses\CharacterClassResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCharacterClass extends EditRecord
{
    protected static string $resource = CharacterClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
