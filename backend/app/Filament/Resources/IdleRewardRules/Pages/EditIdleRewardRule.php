<?php

namespace App\Filament\Resources\IdleRewardRules\Pages;

use App\Filament\Resources\IdleRewardRules\IdleRewardRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIdleRewardRule extends EditRecord
{
    protected static string $resource = IdleRewardRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
