<?php

namespace App\Filament\Resources\Gems\Pages;

use App\Filament\Resources\Gems\GemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGem extends EditRecord
{
    protected static string $resource = GemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
