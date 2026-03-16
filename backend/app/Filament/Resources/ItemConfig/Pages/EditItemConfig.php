<?php

namespace App\Filament\Resources\ItemConfig\Pages;

use App\Filament\Resources\ItemConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditItemConfig extends EditRecord
{
    protected static string $resource = ItemConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
