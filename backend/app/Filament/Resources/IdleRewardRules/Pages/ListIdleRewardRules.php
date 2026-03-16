<?php

namespace App\Filament\Resources\IdleRewardRules\Pages;

use App\Filament\Resources\IdleRewardRules\IdleRewardRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIdleRewardRules extends ListRecords
{
    protected static string $resource = IdleRewardRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新增挂机规则'),
        ];
    }
}
