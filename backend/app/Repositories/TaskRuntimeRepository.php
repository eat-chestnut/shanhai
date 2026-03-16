<?php

namespace App\Repositories;

use App\Models\PlayerTaskProgress;
use App\Models\TaskConfig;
use App\Repositories\Contracts\TaskRuntimeRepositoryInterface;
use Illuminate\Support\Collection;

class TaskRuntimeRepository implements TaskRuntimeRepositoryInterface
{
    public function getOpenTaskConfigs(): Collection
    {
        return TaskConfig::query()
            ->where('is_open', true)
            ->orderBy('task_type')
            ->orderBy('sort')
            ->orderBy('task_id')
            ->get();
    }

    public function findTaskConfig(string $taskId): ?TaskConfig
    {
        return TaskConfig::query()
            ->where('task_id', $taskId)
            ->where('is_open', true)
            ->first();
    }

    public function getPlayerProgress(int $playerId): Collection
    {
        return PlayerTaskProgress::query()
            ->where('player_id', $playerId)
            ->get();
    }

    public function findPlayerProgress(int $playerId, string $taskId, string $cycleKey, bool $forUpdate = false): ?PlayerTaskProgress
    {
        $query = PlayerTaskProgress::query()
            ->where('player_id', $playerId)
            ->where('task_id', $taskId)
            ->where('cycle_key', $cycleKey);

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function upsertPlayerProgress(array $attributes, array $values): PlayerTaskProgress
    {
        return PlayerTaskProgress::query()->updateOrCreate($attributes, $values);
    }
}
