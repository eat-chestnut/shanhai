<?php

namespace App\Filament\Resources\HallFeatures\Pages;

use App\Filament\Resources\HallFeatures\HallFeatureResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHallFeature extends EditRecord
{
    protected static string $resource = HallFeatureResource::class;

    public function getTitle(): string
    {
        return '编辑大厅功能';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
