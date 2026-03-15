<?php

namespace App\Filament\Resources\MainlineNodes\Pages;

use App\Filament\Resources\MainlineNodes\MainlineNodeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMainlineNode extends EditRecord
{
    protected static string $resource = MainlineNodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
