<?php

namespace App\Repositories;

use App\Models\PlayerEquipment;
use App\Repositories\Contracts\EquipmentRuntimeRepositoryInterface;
use Illuminate\Support\Collection;

class EquipmentRuntimeRepository implements EquipmentRuntimeRepositoryInterface
{
    public function countByPlayer(int $playerId): int
    {
        return PlayerEquipment::query()
            ->where('player_id', $playerId)
            ->count();
    }

    public function create(array $attributes): PlayerEquipment
    {
        return PlayerEquipment::query()->create($attributes);
    }

    public function findByUid(int $playerId, string $equipmentUid): ?PlayerEquipment
    {
        return PlayerEquipment::query()
            ->where('player_id', $playerId)
            ->where('equipment_uid', $equipmentUid)
            ->first();
    }

    public function findByUidForUpdate(int $playerId, string $equipmentUid): ?PlayerEquipment
    {
        return PlayerEquipment::query()
            ->where('player_id', $playerId)
            ->where('equipment_uid', $equipmentUid)
            ->lockForUpdate()
            ->first();
    }

    public function findEquippedBySlot(int $playerId, string $slotType): ?PlayerEquipment
    {
        return PlayerEquipment::query()
            ->where('player_id', $playerId)
            ->where('slot_type', $slotType)
            ->where('is_equipped', true)
            ->first();
    }

    public function findEquippedBySlotForUpdate(int $playerId, string $slotType): ?PlayerEquipment
    {
        return PlayerEquipment::query()
            ->where('player_id', $playerId)
            ->where('slot_type', $slotType)
            ->where('is_equipped', true)
            ->lockForUpdate()
            ->first();
    }

    public function update(PlayerEquipment $playerEquipment, array $attributes): PlayerEquipment
    {
        $playerEquipment->fill($attributes);
        $playerEquipment->save();

        return $playerEquipment->refresh();
    }

    public function getByPlayer(int $playerId): Collection
    {
        return PlayerEquipment::query()
            ->where('player_id', $playerId)
            ->orderByDesc('is_equipped')
            ->orderBy('slot_type')
            ->orderBy('created_at')
            ->get();
    }

    public function getEquippedByPlayer(int $playerId): Collection
    {
        return PlayerEquipment::query()
            ->where('player_id', $playerId)
            ->where('is_equipped', true)
            ->orderBy('slot_type')
            ->orderBy('created_at')
            ->get();
    }
}
