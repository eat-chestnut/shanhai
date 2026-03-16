<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Dungeon;
use App\Models\DungeonDifficulty;
use App\Models\PlayerProfile;
use App\Repositories\Contracts\PlayerRuntimeRepositoryInterface;

class DungeonRuntimeService
{
    public function __construct(
        private readonly PlayerRuntimeRepositoryInterface $playerRuntimeRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function listDungeons(PlayerProfile $playerProfile): array
    {
        $dungeons = Dungeon::query()
            ->orderBy('dungeon_id')
            ->get()
            ->map(fn (Dungeon $dungeon): array => $this->buildDungeonPayload($playerProfile, $dungeon, false))
            ->values()
            ->all();

        return [
            'dungeons' => $dungeons,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDungeonDetail(PlayerProfile $playerProfile, string $dungeonId): array
    {
        $dungeon = Dungeon::query()
            ->where('dungeon_id', $dungeonId)
            ->first();

        if (! $dungeon) {
            throw new ApiException('副本不存在', 40421, 404);
        }

        return [
            'dungeon' => $this->buildDungeonPayload($playerProfile, $dungeon, true),
        ];
    }

    /**
     * @return array{dungeon:Dungeon,difficulty:DungeonDifficulty}
     */
    public function assertDungeonAccess(PlayerProfile $playerProfile, string $dungeonId, string $difficultyId): array
    {
        $detail = $this->getDungeonDetail($playerProfile, $dungeonId);
        $dungeonPayload = $detail['dungeon'];

        if (! ($dungeonPayload['is_unlocked'] ?? false)) {
            throw new ApiException('副本未解锁', 40051, 400);
        }

        $difficultyPayload = collect($dungeonPayload['difficulties'] ?? [])->firstWhere('difficulty_id', $difficultyId);

        if (! is_array($difficultyPayload)) {
            throw new ApiException('难度不存在', 40422, 404);
        }

        if (! ($difficultyPayload['is_unlocked'] ?? false)) {
            throw new ApiException('难度未解锁', 40052, 400);
        }

        $dungeon = Dungeon::query()
            ->where('dungeon_id', $dungeonId)
            ->firstOrFail();
        $difficulty = DungeonDifficulty::query()
            ->where('dungeon_id', $dungeonId)
            ->where('difficulty_id', $difficultyId)
            ->first();

        if (! $difficulty) {
            throw new ApiException('难度不存在', 40422, 404);
        }

        return [
            'dungeon' => $dungeon,
            'difficulty' => $difficulty,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDungeonPayload(PlayerProfile $playerProfile, Dungeon $dungeon, bool $includeDifficulties): array
    {
        $isUnlocked = (int) $playerProfile->level >= (int) $dungeon->unlock_level;
        $payload = [
            'dungeon_id' => $dungeon->dungeon_id,
            'dungeon_name' => $dungeon->dungeon_name,
            'unlock_level' => (int) $dungeon->unlock_level,
            'is_unlocked' => $isUnlocked,
        ];

        if (! $includeDifficulties) {
            return $payload;
        }

        $difficulties = DungeonDifficulty::query()
            ->where('dungeon_id', $dungeon->dungeon_id)
            ->orderByRaw("CASE difficulty_id WHEN 'easy' THEN 0 WHEN 'normal' THEN 1 WHEN 'hard' THEN 2 WHEN 'nightmare' THEN 3 ELSE 99 END")
            ->orderBy('difficulty_id')
            ->get();
        $difficultyPayloads = [];

        foreach ($difficulties as $index => $difficulty) {
            $progressRecord = $this->playerRuntimeRepository->findDungeonProgress(
                $playerProfile->player_id,
                $dungeon->dungeon_id,
                $difficulty->difficulty_id,
            );
            $previousDifficulty = $index > 0 ? $difficulties[$index - 1] : null;
            $previousCleared = true;

            if ($previousDifficulty instanceof DungeonDifficulty) {
                $previousProgress = $this->playerRuntimeRepository->findDungeonProgress(
                    $playerProfile->player_id,
                    $dungeon->dungeon_id,
                    $previousDifficulty->difficulty_id,
                );
                $previousCleared = (int) ($previousProgress?->clear_count ?? 0) > 0;
            }

            $difficultyPayloads[] = [
                'difficulty_id' => $difficulty->difficulty_id,
                'dungeon_id' => $difficulty->dungeon_id,
                'recommended_power' => (int) $difficulty->recommended_power,
                'first_clear_reward_group_id' => $difficulty->first_clear_reward_group_id,
                'is_unlocked' => $isUnlocked && $previousCleared,
                'is_first_clear' => (bool) ($progressRecord?->is_first_clear ?? false),
                'clear_count' => (int) ($progressRecord?->clear_count ?? 0),
                'daily_count' => (int) ($progressRecord?->daily_count ?? 0),
            ];
        }

        $payload['difficulties'] = $difficultyPayloads;

        return $payload;
    }
}
