<?php

namespace App\Filament\Resources\MainlineNodes\Pages;

use App\Filament\Resources\MainlineNodes\MainlineNodeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMainlineNode extends CreateRecord
{
    protected static string $resource = MainlineNodeResource::class;

    public function getTitle(): string
    {
        return '新增节点';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
