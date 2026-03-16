<?php

namespace App\Repositories\Contracts;

use App\Models\IdleRewardRule;
use Illuminate\Support\Collection;

interface IdleRuntimeRepositoryInterface
{
    public function getOpenRules(): Collection;

    public function resolveRuleForLevel(int $level): ?IdleRewardRule;
}
