<?php

namespace App\Filament\Resources\MainlineNodes\Pages;

use App\Filament\Resources\MainlineNodes\MainlineNodeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMainlineNode extends EditRecord
{
    protected static string $resource = MainlineNodeResource::class;

    public function getTitle(): string
    {
        return '编辑节点';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
