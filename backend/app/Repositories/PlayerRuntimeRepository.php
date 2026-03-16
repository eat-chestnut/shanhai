<?php

namespace App\Repositories;

use App\Models\PlayerDungeonProgress;
use App\Models\PlayerItem;
use App\Models\PlayerProfile;
use App\Models\PlayerStageProgress;
use App\Repositories\Contracts\PlayerRuntimeRepositoryInterface;
use Illuminate\Support\Collection;

class PlayerRuntimeRepository implements PlayerRuntimeRepositoryInterface
{
    public function findByPlayerId(int $playerId): ?PlayerProfile
    {
        return PlayerProfile::query()
            ->where('player_id', $playerId)
            ->first();
    }

    public function nextPlayerId(): int
    {
        $currentMax = (int) PlayerProfile::query()->max('player_id');

        return max($currentMax + 1, 10001);
    }

    public function createProfile(array $attributes): PlayerProfile
    {
        return PlayerProfile::query()->create($attributes);
    }

    public function updateProfile(PlayerProfile $playerProfile, array $attributes): PlayerProfile
    {
        $playerProfile->fill($attributes);
        $playerProfile->save();

        return $playerProfile->refresh();
    }

    public function refreshProfile(PlayerProfile $playerProfile): PlayerProfile
    {
        return $playerProfile->refresh();
    }

    public function getProfileForUpdate(int $playerId): ?PlayerProfile
    {
        return PlayerProfile::query()
            ->where('player_id', $playerId)
            ->lockForUpdate()
            ->first();
    }

    public function syncInventory(int $playerId, array $rows): void
    {
        PlayerItem::query()->where('player_id', $playerId)->delete();

        if ($rows === []) {
            return;
        }

        PlayerItem::query()->insert($rows);
    }

    public function getInventory(int $playerId): Collection
    {
        return PlayerItem::query()
            ->where('player_id', $playerId)
            ->where('count', '>', 0)
            ->orderBy('item_id')
            ->get();
    }

    public function findInventoryItem(int $playerId, string $itemId): ?PlayerItem
    {
        return PlayerItem::query()
            ->where('player_id', $playerId)
            ->where('item_id', $itemId)
            ->first();
    }

    public function incrementItemCount(int $playerId, string $itemId, int $count): PlayerItem
    {
        $item = PlayerItem::query()->firstOrCreate(
            [
                'player_id' => $playerId,
                'item_id' => $itemId,
            ],
            [
                'count' => 0,
            ],
        );

        $item->forceFill([
            'count' => max((int) $item->count + $count, 0),
        ])->save();

        return $item->refresh();
    }

    public function getStageProgress(int $playerId): Collection
    {
        return PlayerStageProgress::query()
            ->where('player_id', $playerId)
            ->orderBy('chapter_id')
            ->orderBy('node_id')
            ->orderBy('difficulty_id')
            ->get();
    }

    public function findStageProgress(int $playerId, string $nodeId, string $difficultyId): ?PlayerStageProgress
    {
        return PlayerStageProgress::query()
            ->where('player_id', $playerId)
            ->where('node_id', $nodeId)
            ->where('difficulty_id', $difficultyId)
            ->first();
    }

    public function upsertStageProgress(array $attributes, array $values): PlayerStageProgress
    {
        return PlayerStageProgress::query()->updateOrCreate($attributes, $values);
    }

    public function getDungeonProgress(int $playerId): Collection
    {
        return PlayerDungeonProgress::query()
            ->where('player_id', $playerId)
            ->orderBy('dungeon_id')
            ->orderByRaw("CASE difficulty_id WHEN 'easy' THEN 0 WHEN 'normal' THEN 1 WHEN 'hard' THEN 2 WHEN 'nightmare' THEN 3 ELSE 99 END")
            ->orderBy('difficulty_id')
            ->get();
    }

    public function findDungeonProgress(int $playerId, string $dungeonId, string $difficultyId): ?PlayerDungeonProgress
    {
        return PlayerDungeonProgress::query()
            ->where('player_id', $playerId)
            ->where('dungeon_id', $dungeonId)
            ->where('difficulty_id', $difficultyId)
            ->first();
    }

    public function upsertDungeonProgress(array $attributes, array $values): PlayerDungeonProgress
    {
        return PlayerDungeonProgress::query()->updateOrCreate($attributes, $values);
    }
}
