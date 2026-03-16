<?php

namespace App\Repositories\Contracts;

use App\Models\PlayerTaskProgress;
use App\Models\TaskConfig;
use Illuminate\Support\Collection;

interface TaskRuntimeRepositoryInterface
{
    public function getOpenTaskConfigs(): Collection;

    public function findTaskConfig(string $taskId): ?TaskConfig;

    public function getPlayerProgress(int $playerId): Collection;

    public function findPlayerProgress(int $playerId, string $taskId, string $cycleKey, bool $forUpdate = false): ?PlayerTaskProgress;

    public function upsertPlayerProgress(array $attributes, array $values): PlayerTaskProgress;
}
