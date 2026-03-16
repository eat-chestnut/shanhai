<?php

namespace App\Filament\Resources\MainlineDifficulties\Pages;

use App\Filament\Resources\MainlineDifficulties\MainlineDifficultyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMainlineDifficulty extends EditRecord
{
    protected static string $resource = MainlineDifficultyResource::class;

    public function getTitle(): string
    {
        return '编辑主线难度';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
