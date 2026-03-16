<?php

namespace App\Repositories;

use App\Models\IdleRewardRule;
use App\Repositories\Contracts\IdleRuntimeRepositoryInterface;
use Illuminate\Support\Collection;

class IdleRuntimeRepository implements IdleRuntimeRepositoryInterface
{
    public function getOpenRules(): Collection
    {
        return IdleRewardRule::query()
            ->where('is_open', true)
            ->orderBy('sort')
            ->orderBy('min_level')
            ->get();
    }

    public function resolveRuleForLevel(int $level): ?IdleRewardRule
    {
        return IdleRewardRule::query()
            ->where('is_open', true)
            ->where('min_level', '<=', $level)
            ->where('max_level', '>=', $level)
            ->orderBy('sort')
            ->orderByDesc('min_level')
            ->first();
    }
}
