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

    /**
     * @return array<string, mixed>
     */
    public function getDungeonSpawnRules(PlayerProfile $playerProfile, string $dungeonId, string $difficultyId): array
    {
        $difficulty = DungeonDifficulty::query()
            ->where('dungeon_id', $dungeonId)
            ->where('difficulty_id', $difficultyId)
            ->first();

        if (! $difficulty) {
            throw new ApiException('难度不存在', 40422, 404);
        }

        return [
            'normal_monster_pool' => $difficulty->normal_monster_pool ?? [],
            'elite_monster_pool' => $difficulty->elite_monster_pool ?? [],
            'boss_monster_pool' => $difficulty->boss_monster_pool ?? [],
            'normal_spawn_interval' => (int) ($difficulty->normal_spawn_interval ?? 5),
            'normal_spawn_count' => (int) ($difficulty->normal_spawn_count ?? 1),
            'max_normal_on_screen' => (int) ($difficulty->max_normal_on_screen ?? 5),
            'elite_trigger_kills' => (int) ($difficulty->elite_trigger_kills ?? 10),
            'boss_trigger_elite_kills' => (int) ($difficulty->boss_trigger_elite_kills ?? 3),
            'stop_spawning_after_boss' => (bool) ($difficulty->stop_spawning_after_boss ?? true),
            'clear_dungeon_after_boss' => (bool) ($difficulty->clear_dungeon_after_boss ?? true),
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
        $meta = $this->resolveDungeonMeta($playerProfile, $dungeon);
        $isUnlocked = (bool) $meta['is_unlocked'];
        $dailyLimit = (int) $meta['daily_limit'];
        $dailyCount = (int) $progressRecords->sum('daily_count');
        $payload = [
            'dungeon_id' => $dungeon->dungeon_id,
            'dungeon_name' => $dungeon->dungeon_name,
            'unlock_level' => (int) $dungeon->unlock_level,
            'is_unlocked' => $isUnlocked,
            'unlock_text' => (string) $meta['unlock_text'],
            'dungeon_desc' => (string) $meta['dungeon_desc'],
            'main_rewards' => array_values($meta['main_rewards'] ?? []),
            'daily_count' => $dailyCount,
            'daily_limit' => $dailyLimit,
            'remaining_count' => max($dailyLimit - $dailyCount, 0),
            'current_tier' => 'easy',
            'is_recommended' => true,
            'suggestion_text' => '可前往试炼。',
        ];

        if (! $includeDifficulties) {
            return $payload;
        }

        $difficulties = DungeonDifficulty::query()
            ->where('dungeon_id', $dungeon->dungeon_id)
            ->orderByRaw("CASE difficulty_id WHEN 'easy' THEN 0 WHEN 'normal' THEN 1 WHEN 'hard' THEN 2 WHEN 'nightmare' THEN 3 WHEN 'epic' THEN 4 ELSE 99 END")
            ->orderBy('difficulty_id')
            ->get();
        $difficultyPayloads = [];
        $firstClearState = [];
        $highestUnlockedDifficultyId = 'easy';
        $highestClearedDifficultyId = '';

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
            $difficultyUnlocked = $isUnlocked && $previousCleared;

            if ($difficultyUnlocked) {
                $highestUnlockedDifficultyId = $difficulty->difficulty_id;
            }

            if ((int) ($progressRecord?->clear_count ?? 0) > 0) {
                $highestClearedDifficultyId = $difficulty->difficulty_id;
            }

            $mainRewards = $this->resolveDifficultyRewards(
                $dungeon,
                $difficulty,
                $meta['main_rewards'] ?? [],
            );
            $recommendedPower = (int) $difficulty->recommended_power;
            $powerGap = (int) $playerProfile->power - $recommendedPower;

            $difficultyPayloads[] = [
                'difficulty_id' => $difficulty->difficulty_id,
                'dungeon_id' => $difficulty->dungeon_id,
                'recommended_power' => $recommendedPower,
                'first_clear_reward_group_id' => $difficulty->first_clear_reward_group_id,
                'is_unlocked' => $difficultyUnlocked,
                'is_first_clear' => $isFirstClear,
                'clear_count' => (int) ($progressRecord?->clear_count ?? 0),
                'daily_count' => (int) ($progressRecord?->daily_count ?? 0),
                'tier_label' => $this->difficultyLabel($difficulty->difficulty_id),
                'main_rewards' => $mainRewards,
                'is_recommended' => $powerGap >= -max((int) round($recommendedPower * 0.08), 20),
                'recommendation_text' => $powerGap >= 0
                    ? '战力已达标，可稳定挑战。'
                    : sprintf('建议再提升 %d 战力。', abs($powerGap)),
            ];
        }

        $payload['difficulties'] = $difficultyPayloads;
        $payload['recommended_power'] = (int) ($difficultyPayloads[0]['recommended_power'] ?? 0);
        $payload['first_clear_state'] = $firstClearState;
        $payload['current_tier'] = $highestClearedDifficultyId !== '' ? $highestClearedDifficultyId : $highestUnlockedDifficultyId;
        $currentTierPayload = collect($difficultyPayloads)->firstWhere('difficulty_id', $payload['current_tier']);
        $payload['is_recommended'] = is_array($currentTierPayload) ? (bool) ($currentTierPayload['is_recommended'] ?? true) : true;
        $payload['suggestion_text'] = is_array($currentTierPayload) ? (string) ($currentTierPayload['recommendation_text'] ?? '可前往试炼。') : '可前往试炼。';

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveDungeonMeta(PlayerProfile $playerProfile, Dungeon $dungeon): array
    {
        $configMeta = $this->getDungeonRuntimeMeta($dungeon->dungeon_id);
        $unlockStageNodeId = (string) ($dungeon->unlock_stage_node_id ?: ($configMeta['unlock_stage_node_id'] ?? ''));
        $levelUnlocked = (int) $playerProfile->level >= (int) $dungeon->unlock_level;
        $stageUnlocked = $unlockStageNodeId === '' || $this->isStageNodeCleared((int) $playerProfile->player_id, $unlockStageNodeId);
        $isUnlocked = $levelUnlocked && $stageUnlocked;
        $unlockText = '已解锁';

        if (! $levelUnlocked) {
            $unlockText = sprintf('达到 Lv.%d 解锁', (int) $dungeon->unlock_level);
        } elseif (! $stageUnlocked) {
            $unlockText = sprintf('通关主线 %s 后解锁', $unlockStageNodeId);
        }

        return [
            'is_unlocked' => $isUnlocked,
            'unlock_text' => $unlockText,
            'dungeon_desc' => (string) ($dungeon->dungeon_desc ?: ($configMeta['dungeon_desc'] ?? '暂无说明')),
            'main_rewards' => array_values($dungeon->main_rewards ?? $configMeta['main_rewards'] ?? []),
            'daily_limit' => max((int) ($dungeon->daily_limit ?: ($configMeta['daily_limit'] ?? config('game_runtime.daily_dungeon_limit', 3))), 1),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getDungeonRuntimeMeta(string $dungeonId): array
    {
        $meta = config(sprintf('game_runtime.dungeon_runtime.%s', $dungeonId), []);

        return is_array($meta) ? $meta : [];
    }

    /**
     * @param  list<string>  $dungeonMainRewards
     * @return list<string>
     */
    private function resolveDifficultyRewards(Dungeon $dungeon, DungeonDifficulty $difficulty, array $dungeonMainRewards): array
    {
        $rewardGroups = config('game_runtime.reward_groups', []);
        $groupRewards = $rewardGroups[$difficulty->first_clear_reward_group_id] ?? [];
        $rewardIds = collect($groupRewards)
            ->map(static fn (array $entry): string => (string) ($entry['item_id'] ?? ''))
            ->filter()
            ->values()
            ->all();

        if ($rewardIds !== []) {
            return array_values(array_unique(array_merge($dungeonMainRewards, $rewardIds)));
        }

        return array_values($dungeonMainRewards);
    }

    private function difficultyLabel(string $difficultyId): string
    {
        return match ($difficultyId) {
            'easy' => '初阶',
            'normal' => '进阶',
            'hard' => '险境',
            'nightmare' => '噩梦',
            default => strtoupper($difficultyId),
        };
    }

    private function isStageNodeCleared(int $playerId, string $nodeId): bool
    {
        return $this->playerRuntimeRepository
            ->getStageProgress($playerId)
            ->contains(static fn ($progress): bool => $progress->node_id === $nodeId && (int) $progress->clear_count > 0);
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
