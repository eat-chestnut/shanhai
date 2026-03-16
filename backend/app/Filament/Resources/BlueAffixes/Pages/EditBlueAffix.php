<?php

namespace App\Filament\Resources\BlueAffixes\Pages;

use App\Filament\Resources\BlueAffixes\BlueAffixResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBlueAffix extends EditRecord
{
    protected static string $resource = BlueAffixResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
