<?php

namespace App\Filament\Resources\RarityConfig\Pages;

use App\Filament\Resources\RarityConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRarityConfig extends EditRecord
{
    protected static string $resource = RarityConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
