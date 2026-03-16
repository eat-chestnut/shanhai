<?php

namespace App\Repositories\Contracts;

use App\Models\PlayerEquipment;
use Illuminate\Support\Collection;

interface EquipmentRuntimeRepositoryInterface
{
    public function countByPlayer(int $playerId): int;

    public function create(array $attributes): PlayerEquipment;

    public function findByUid(int $playerId, string $equipmentUid): ?PlayerEquipment;

    public function findByUidForUpdate(int $playerId, string $equipmentUid): ?PlayerEquipment;

    public function findEquippedBySlot(int $playerId, string $slotType): ?PlayerEquipment;

    public function findEquippedBySlotForUpdate(int $playerId, string $slotType): ?PlayerEquipment;

    public function update(PlayerEquipment $playerEquipment, array $attributes): PlayerEquipment;

    public function getByPlayer(int $playerId): Collection;

    public function getEquippedByPlayer(int $playerId): Collection;
}
