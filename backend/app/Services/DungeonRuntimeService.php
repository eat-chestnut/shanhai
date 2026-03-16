<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Dungeon;
use App\Models\DungeonDifficulty;
use App\Models\PlayerDungeonProgress;
use App\Models\PlayerProfile;
use App\Repositories\Contracts\PlayerRuntimeRepositoryInterface;
use Illuminate\Support\Carbon;

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

        if ((int) ($dungeonPayload['remaining_count'] ?? 0) <= 0) {
            throw new ApiException('副本次数不足', 40053, 400);
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

    public function assertDungeonSettlementAvailable(PlayerProfile $playerProfile, string $dungeonId): void
    {
        $detail = $this->getDungeonDetail($playerProfile, $dungeonId);

        if ((int) ($detail['dungeon']['remaining_count'] ?? 0) <= 0) {
            throw new ApiException('副本次数不足', 40053, 400);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDungeonPayload(PlayerProfile $playerProfile, Dungeon $dungeon, bool $includeDifficulties): array
    {
        $progressRecords = $this->playerRuntimeRepository
            ->getDungeonProgress((int) $playerProfile->player_id)
            ->filter(fn (PlayerDungeonProgress $progress): bool => $progress->dungeon_id === $dungeon->dungeon_id)
            ->map(function (PlayerDungeonProgress $progress): PlayerDungeonProgress {
                return $this->normalizeDailyProgressRecord($progress);
            })
            ->values();
        $meta = $this->getDungeonRuntimeMeta($dungeon->dungeon_id);
        $isUnlocked = (int) $playerProfile->level >= (int) $dungeon->unlock_level;
        $dailyLimit = (int) ($meta['daily_limit'] ?? config('game_runtime.daily_dungeon_limit', 3));
        $dailyCount = (int) $progressRecords->sum('daily_count');
        $payload = [
            'dungeon_id' => $dungeon->dungeon_id,
            'dungeon_name' => $dungeon->dungeon_name,
            'unlock_level' => (int) $dungeon->unlock_level,
            'is_unlocked' => $isUnlocked,
            'unlock_text' => $isUnlocked ? '已解锁' : sprintf('达到 Lv.%d 解锁', (int) $dungeon->unlock_level),
            'dungeon_desc' => (string) ($meta['dungeon_desc'] ?? '暂无说明'),
            'main_rewards' => array_values($meta['main_rewards'] ?? []),
            'daily_count' => $dailyCount,
            'daily_limit' => $dailyLimit,
            'remaining_count' => max($dailyLimit - $dailyCount, 0),
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
        $firstClearState = [];

        foreach ($difficulties as $index => $difficulty) {
            $progressRecord = $progressRecords->first(
                fn (PlayerDungeonProgress $progress): bool => $progress->difficulty_id === $difficulty->difficulty_id,
            );
            $previousDifficulty = $index > 0 ? $difficulties[$index - 1] : null;
            $previousCleared = true;

            if ($previousDifficulty instanceof DungeonDifficulty) {
                $previousProgress = $progressRecords->first(
                    fn (PlayerDungeonProgress $progress): bool => $progress->difficulty_id === $previousDifficulty->difficulty_id,
                );
                $previousCleared = (int) ($previousProgress?->clear_count ?? 0) > 0;
            }

            $isFirstClear = (bool) ($progressRecord?->is_first_clear ?? false);
            $firstClearState[$difficulty->difficulty_id] = $isFirstClear;

            $difficultyPayloads[] = [
                'difficulty_id' => $difficulty->difficulty_id,
                'dungeon_id' => $difficulty->dungeon_id,
                'recommended_power' => (int) $difficulty->recommended_power,
                'first_clear_reward_group_id' => $difficulty->first_clear_reward_group_id,
                'is_unlocked' => $isUnlocked && $previousCleared,
                'is_first_clear' => $isFirstClear,
                'clear_count' => (int) ($progressRecord?->clear_count ?? 0),
                'daily_count' => (int) ($progressRecord?->daily_count ?? 0),
            ];
        }

        $payload['difficulties'] = $difficultyPayloads;
        $payload['recommended_power'] = (int) ($difficultyPayloads[0]['recommended_power'] ?? 0);
        $payload['first_clear_state'] = $firstClearState;

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function getDungeonRuntimeMeta(string $dungeonId): array
    {
        $meta = config(sprintf('game_runtime.dungeon_runtime.%s', $dungeonId), []);

        return is_array($meta) ? $meta : [];
    }

    private function normalizeDailyProgressRecord(PlayerDungeonProgress $progress): PlayerDungeonProgress
    {
        $today = Carbon::now()->toDateString();

        if ($progress->daily_reset_at instanceof Carbon && $progress->daily_reset_at->toDateString() === $today) {
            return $progress;
        }

        $progress->forceFill([
            'daily_count' => 0,
            'daily_reset_at' => Carbon::now()->startOfDay(),
        ])->save();

        return $progress->refresh();
    }
}
