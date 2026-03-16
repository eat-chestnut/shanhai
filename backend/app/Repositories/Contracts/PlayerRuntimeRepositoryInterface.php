<?php

namespace App\Repositories\Contracts;

use App\Models\PlayerDungeonProgress;
use App\Models\PlayerItem;
use App\Models\PlayerProfile;
use App\Models\PlayerStageProgress;
use Illuminate\Support\Collection;

interface PlayerRuntimeRepositoryInterface
{
    public function findByPlayerId(int $playerId): ?PlayerProfile;

    public function nextPlayerId(): int;

    public function createProfile(array $attributes): PlayerProfile;

    public function updateProfile(PlayerProfile $playerProfile, array $attributes): PlayerProfile;

    public function refreshProfile(PlayerProfile $playerProfile): PlayerProfile;

    public function getProfileForUpdate(int $playerId): ?PlayerProfile;

    public function syncInventory(int $playerId, array $rows): void;

    public function syncStageProgress(int $playerId, array $rows): void;

    public function getInventory(int $playerId): Collection;

    public function findInventoryItem(int $playerId, string $itemId): ?PlayerItem;

    public function incrementItemCount(int $playerId, string $itemId, int $count): PlayerItem;

    public function getStageProgress(int $playerId): Collection;

    public function findStageProgress(int $playerId, string $nodeId, string $difficultyId): ?PlayerStageProgress;

    public function upsertStageProgress(array $attributes, array $values): PlayerStageProgress;

    public function getDungeonProgress(int $playerId): Collection;

    public function findDungeonProgress(int $playerId, string $dungeonId, string $difficultyId): ?PlayerDungeonProgress;

    public function upsertDungeonProgress(array $attributes, array $values): PlayerDungeonProgress;
}
