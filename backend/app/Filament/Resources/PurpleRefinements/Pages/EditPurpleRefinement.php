<?php

namespace App\Filament\Resources\PurpleRefinements\Pages;

use App\Filament\Resources\PurpleRefinements\PurpleRefinementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPurpleRefinement extends EditRecord
{
    protected static string $resource = PurpleRefinementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
