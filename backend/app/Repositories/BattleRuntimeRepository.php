<?php

namespace App\Repositories;

use App\Models\BattleRecord;
use App\Repositories\Contracts\BattleRuntimeRepositoryInterface;

class BattleRuntimeRepository implements BattleRuntimeRepositoryInterface
{
    public function create(array $attributes): BattleRecord
    {
        return BattleRecord::query()->create($attributes);
    }

    public function findByBattleId(string $battleId): ?BattleRecord
    {
        return BattleRecord::query()
            ->where('battle_id', $battleId)
            ->first();
    }

    public function findByBattleIdForUpdate(string $battleId): ?BattleRecord
    {
        return BattleRecord::query()
            ->where('battle_id', $battleId)
            ->lockForUpdate()
            ->first();
    }

    public function update(BattleRecord $battleRecord, array $attributes): BattleRecord
    {
        $battleRecord->fill($attributes);
        $battleRecord->save();

        return $battleRecord->refresh();
    }
}
