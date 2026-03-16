<?php

namespace App\Filament\Resources\RarityConfigs\Pages;

use App\Filament\Resources\RarityConfigs\RarityConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRarityConfig extends EditRecord
{
    protected static string $resource = RarityConfigResource::class;

    public function getTitle(): string
    {
        return '编辑稀有度';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
