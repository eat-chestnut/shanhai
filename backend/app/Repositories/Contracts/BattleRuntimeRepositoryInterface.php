<?php

namespace App\Repositories\Contracts;

use App\Models\BattleRecord;

interface BattleRuntimeRepositoryInterface
{
    public function create(array $attributes): BattleRecord;

    public function findByBattleId(string $battleId): ?BattleRecord;

    public function findByBattleIdForUpdate(string $battleId): ?BattleRecord;

    public function update(BattleRecord $battleRecord, array $attributes): BattleRecord;
}
