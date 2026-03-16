<?php

namespace App\Filament\Resources\ChallengeConfigs\Pages;

use App\Filament\Resources\ChallengeConfigs\ChallengeConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChallengeConfig extends EditRecord
{
    protected static string $resource = ChallengeConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }
}
