<?php

namespace App\Filament\Resources\EquipmentSets\Pages;

use App\Filament\Resources\EquipmentSets\EquipmentSetResource;
use App\Models\EquipmentSet;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEquipmentSet extends EditRecord
{
    protected static string $resource = EquipmentSetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['effects'] = EquipmentSet::normalizeEffectsPayload($data['effects'] ?? []);

        return $data;
    }
}
