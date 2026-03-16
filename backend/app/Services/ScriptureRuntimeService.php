<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\MainlineChapter;
use App\Models\PlayerProfile;
use App\Models\Scripture;
use App\Models\ScriptureDropTag;
use App\Models\ScriptureMonster;
use App\Models\ScriptureUpgradeCost;
use App\Models\ScriptureWorldTier;
use App\Repositories\Contracts\PlayerRuntimeRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ScriptureRuntimeService
{
    public function __construct(
        private readonly PlayerRuntimeRepositoryInterface $playerRuntimeRepository,
        private readonly InventoryService $inventoryService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function listScriptures(PlayerProfile $playerProfile): array
    {
        $progressRecords = $this->playerRuntimeRepository->getScriptureProgress((int) $playerProfile->player_id)
            ->keyBy('scripture_id');

        $scriptures = Scripture::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('scripture_id')
            ->get()
            ->map(fn (Scripture $scripture): array => $this->buildScriptureListPayload(
                $playerProfile,
                $scripture,
                $progressRecords->get($scripture->scripture_id),
            ))
            ->values()
            ->all();

        return [
            'scriptures' => $scriptures,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getScriptureDetail(PlayerProfile $playerProfile, string $scriptureId): array
    {
        $scripture = $this->resolveScriptureOrFail($scriptureId);
        $payload = $this->buildScriptureListPayload(
            $playerProfile,
            $scripture,
            $this->playerRuntimeRepository->findScriptureProgress((int) $playerProfile->player_id, $scriptureId),
        );

        if (! $payload['is_unlocked']) {
            throw new ApiException('经卷未解锁', 40011, 400);
        }

        $progressState = $this->getProgressState($playerProfile, $scriptureId);
        $maxUnlockedWorldLevel = (int) $progressState['max_unlocked_world_level'];

        $tierPreview = ScriptureWorldTier::query()
            ->where('scripture_id', $scriptureId)
            ->orderBy('world_level_start')
            ->orderBy('world_level_end')
            ->get()
            ->filter(static fn (ScriptureWorldTier $tier): bool => (int) $tier->world_level_start <= $maxUnlockedWorldLevel)
            ->map(static fn (ScriptureWorldTier $tier): array => [
                'world_level_start' => (int) $tier->world_level_start,
                'world_level_end' => (int) $tier->world_level_end,
                'hp_scale' => (float) $tier->hp_scale,
                'atk_scale' => (float) $tier->atk_scale,
                'def_scale' => (float) $tier->def_scale,
                'reward_scale' => (float) $tier->reward_scale,
                'gold_scale' => (float) $tier->gold_scale,
                'normal_monster_ids' => array_values($tier->normal_monster_ids ?? []),
                'elite_monster_ids' => array_values($tier->elite_monster_ids ?? []),
                'boss_monster_ids' => array_values($tier->boss_monster_ids ?? []),
                'extra_drop_tags' => array_values($tier->extra_drop_tags ?? []),
                'new_feature_note' => (string) ($tier->new_feature_note ?? ''),
            ])
            ->values()
            ->all();

        $upgradeCostPreview = $this->nextUpgradeCosts($scriptureId, (int) $progressState['current_world_level']);

        return [
            'scripture_id' => $scripture->scripture_id,
            'scripture_name' => $scripture->scripture_name,
            'scripture_group' => $scripture->scripture_group,
            'current_world_level' => (int) $progressState['current_world_level'],
            'max_unlocked_world_level' => $maxUnlockedWorldLevel,
            'available_world_levels' => $maxUnlockedWorldLevel > 0 ? range(1, $maxUnlockedWorldLevel) : [],
            'tier_preview' => $tierPreview,
            'upgrade_cost_preview' => $upgradeCostPreview,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function upgradeScripture(PlayerProfile $playerProfile, string $scriptureId, int $targetWorldLevel): array
    {
        $scripture = $this->resolveScriptureOrFail($scriptureId);
        $payload = $this->buildScriptureListPayload(
            $playerProfile,
            $scripture,
            $this->playerRuntimeRepository->findScriptureProgress((int) $playerProfile->player_id, $scriptureId),
        );

        if (! $payload['is_unlocked']) {
            throw new ApiException('经卷未解锁', 40011, 400);
        }

        return DB::transaction(function () use ($playerProfile, $scriptureId, $targetWorldLevel): array {
            $lockedProfile = $this->playerRuntimeRepository->getProfileForUpdate((int) $playerProfile->player_id);

            if (! $lockedProfile) {
                throw new ApiException('玩家不存在', 40442, 404);
            }

            $progressState = $this->getProgressState($lockedProfile, $scriptureId);
            $nextUpgrade = $this->resolveNextUpgradeCost($scriptureId, (int) $progressState['current_world_level']);

            if (! $nextUpgrade || (int) $nextUpgrade->target_world_level !== $targetWorldLevel) {
                throw new ApiException('目标等级未配置升级成本', 4001, 400);
            }

            if ((int) $lockedProfile->level < (int) $nextUpgrade->required_player_level) {
                throw new ApiException('玩家等级不足', 40012, 400);
            }

            $inventoryUpdate = [];

            foreach ($nextUpgrade->cost_items ?? [] as $costItem) {
                $itemId = (string) ($costItem['item_id'] ?? '');
                $count = max((int) ($costItem['count'] ?? 0), 0);

                if ($itemId === '' || $count <= 0) {
                    continue;
                }

                $this->inventoryService->consumeItem((int) $lockedProfile->player_id, $itemId, $count, '经卷升级材料不足');
                $inventoryUpdate[] = [
                    'item_id' => $itemId,
                    'delta' => -$count,
                ];
            }

            $this->inventoryService->spendCurrency($lockedProfile, 'gold', (int) $nextUpgrade->cost_gold, '金币不足');

            $this->playerRuntimeRepository->upsertScriptureProgress(
                [
                    'player_id' => (int) $lockedProfile->player_id,
                    'scripture_id' => $scriptureId,
                ],
                [
                    'current_world_level' => $targetWorldLevel,
                    'max_unlocked_world_level' => max((int) $progressState['max_unlocked_world_level'], $targetWorldLevel),
                ],
            );

            return [
                'scripture_id' => $scriptureId,
                'previous_world_level' => (int) $progressState['current_world_level'],
                'current_world_level' => $targetWorldLevel,
                'inventory_update' => $inventoryUpdate,
                'currencies_update' => [
                    'gold_coin' => -((int) $nextUpgrade->cost_gold),
                ],
            ];
        });
    }

    /**
     * @return array{scripture:Scripture,tier:ScriptureWorldTier,current_world_level:int,max_unlocked_world_level:int}
     */
    public function assertScriptureBattleAccess(PlayerProfile $playerProfile, string $scriptureId, int $worldLevel): array
    {
        $scripture = $this->resolveScriptureOrFail($scriptureId);
        $payload = $this->buildScriptureListPayload(
            $playerProfile,
            $scripture,
            $this->playerRuntimeRepository->findScriptureProgress((int) $playerProfile->player_id, $scriptureId),
        );

        if (! $payload['is_unlocked']) {
            throw new ApiException('经卷未解锁', 40011, 400);
        }

        $progressState = $this->getProgressState($playerProfile, $scriptureId);
        $maxUnlockedWorldLevel = (int) $progressState['max_unlocked_world_level'];

        if ($worldLevel <= 0 || $worldLevel > $maxUnlockedWorldLevel) {
            throw new ApiException('世界等级未解锁', 40013, 400);
        }

        $tier = $this->resolveWorldTierOrFail($scriptureId, $worldLevel);

        return [
            'scripture' => $scripture,
            'tier' => $tier,
            'current_world_level' => (int) $progressState['current_world_level'],
            'max_unlocked_world_level' => $maxUnlockedWorldLevel,
        ];
    }

    /**
     * @return array{current_world_level:int,max_unlocked_world_level:int}
     */
    public function getProgressState(PlayerProfile $playerProfile, string $scriptureId): array
    {
        $scripture = $this->resolveScriptureOrFail($scriptureId);
        $progress = $this->playerRuntimeRepository->findScriptureProgress((int) $playerProfile->player_id, $scriptureId);
        $isUnlocked = $this->checkUnlock($playerProfile, $scripture);

        if (! $isUnlocked) {
            return [
                'current_world_level' => 0,
                'max_unlocked_world_level' => 0,
            ];
        }

        $currentWorldLevel = max((int) ($progress?->current_world_level ?? 0), 1);
        $maxUnlockedWorldLevel = max((int) ($progress?->max_unlocked_world_level ?? 0), 1);

        return [
            'current_world_level' => $currentWorldLevel,
            'max_unlocked_world_level' => $maxUnlockedWorldLevel,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildBattlePayload(string $scriptureId, int $worldLevel): array
    {
        $tier = $this->resolveWorldTierOrFail($scriptureId, $worldLevel);
        $normalMonsterIds = array_values($tier->normal_monster_ids ?? []);
        $eliteMonsterIds = array_values($tier->elite_monster_ids ?? []);
        $bossMonsterIds = array_values($tier->boss_monster_ids ?? []);
        $monsterIds = array_values(array_merge($normalMonsterIds, $eliteMonsterIds, $bossMonsterIds));
        $monsters = ScriptureMonster::query()
            ->whereIn('monster_id', $monsterIds)
            ->where('is_enabled', true)
            ->get()
            ->mapWithKeys(static fn (ScriptureMonster $monster): array => [
                $monster->monster_id => $monster,
            ]);

        return [
            'enemy_group_snapshot' => [
                'monster_group_id' => sprintf('scripture_%s_lv_%d', $scriptureId, $worldLevel),
                'normal_monster_ids' => $normalMonsterIds,
                'elite_monster_ids' => $eliteMonsterIds,
                'boss_monster_ids' => $bossMonsterIds,
                'monsters' => collect($monsterIds)
                    ->map(fn (string $monsterId): ?array => $this->buildBattleMonsterPayload($monsters->get($monsterId), $tier))
                    ->filter()
                    ->values()
                    ->all(),
            ],
            'battle_rules_snapshot' => [
                'hp_scale' => (float) $tier->hp_scale,
                'atk_scale' => (float) $tier->atk_scale,
                'def_scale' => (float) $tier->def_scale,
                'reward_scale' => (float) $tier->reward_scale,
                'gold_scale' => (float) $tier->gold_scale,
                'extra_drop_tags' => array_values($tier->extra_drop_tags ?? []),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function resolveBattleRewards(string $scriptureId, int $worldLevel, int $battleSeed): array
    {
        $tier = $this->resolveWorldTierOrFail($scriptureId, $worldLevel);
        $rewardScale = max((float) $tier->reward_scale, 0.0);
        $rewards = [];

        foreach ($tier->extra_drop_tags ?? [] as $index => $dropTag) {
            $tag = ScriptureDropTag::query()->find((string) $dropTag);

            if (! $tag) {
                continue;
            }

            $entry = $this->selectDropTagItem($tag, $battleSeed, $index);

            if ($entry === null) {
                continue;
            }

            $baseCount = $this->rollDropTagCount($entry, $battleSeed, $index);
            $count = max((int) round($baseCount * ($rewardScale > 0 ? $rewardScale : 1.0)), 1);

            $rewards[] = [
                'item_id' => (string) $entry['item_id'],
                'count' => $count,
            ];
        }

        return $this->mergeRewards($rewards);
    }

    private function resolveScriptureOrFail(string $scriptureId): Scripture
    {
        $scripture = Scripture::query()
            ->where('scripture_id', $scriptureId)
            ->where('is_enabled', true)
            ->first();

        if (! $scripture) {
            throw new ApiException('经卷不存在', 40411, 404);
        }

        return $scripture;
    }

    private function resolveWorldTierOrFail(string $scriptureId, int $worldLevel): ScriptureWorldTier
    {
        $tier = ScriptureWorldTier::query()
            ->where('scripture_id', $scriptureId)
            ->orderBy('world_level_start')
            ->orderBy('world_level_end')
            ->get()
            ->first(static fn (ScriptureWorldTier $entry): bool => $entry->matchesWorldLevel($worldLevel));

        if (! $tier instanceof ScriptureWorldTier) {
            throw new ApiException('世界等级区间不存在', 40412, 404);
        }

        return $tier;
    }

    /**
     * @param  object|null  $progress
     * @return array<string, mixed>
     */
    private function buildScriptureListPayload(PlayerProfile $playerProfile, Scripture $scripture, ?object $progress): array
    {
        $isUnlocked = $this->checkUnlock($playerProfile, $scripture);
        $currentWorldLevel = $isUnlocked ? max((int) ($progress?->current_world_level ?? 0), 1) : 0;
        $maxUnlockedWorldLevel = $isUnlocked ? max((int) ($progress?->max_unlocked_world_level ?? 0), 1) : 0;

        return [
            'scripture_id' => $scripture->scripture_id,
            'scripture_name' => $scripture->scripture_name,
            'scripture_group' => $scripture->scripture_group,
            'is_unlocked' => $isUnlocked,
            'unlock_text' => $isUnlocked ? '' : $this->buildUnlockText($playerProfile, $scripture),
            'current_world_level' => $currentWorldLevel,
            'max_unlocked_world_level' => $maxUnlockedWorldLevel,
        ];
    }

    private function checkUnlock(PlayerProfile $playerProfile, Scripture $scripture): bool
    {
        $unlockCondition = $scripture->unlock_condition ?? [];
        $requiredChapterId = (string) ($unlockCondition['clear_chapter_id'] ?? '');
        $requiredPlayerLevel = (int) ($unlockCondition['player_level'] ?? 1);
        $isLevelSatisfied = (int) $playerProfile->level >= $requiredPlayerLevel;

        if (! $isLevelSatisfied) {
            return false;
        }

        if ($requiredChapterId === '') {
            return true;
        }

        $completionNode = MainlineChapter::query()
            ->where('chapter_id', $requiredChapterId)
            ->first();

        if (! $completionNode) {
            return false;
        }

        return $this->playerRuntimeRepository
            ->getStageProgress((int) $playerProfile->player_id)
            ->contains(static fn ($progress): bool => $progress->chapter_id === $requiredChapterId && (int) $progress->clear_count > 0);
    }

    private function buildUnlockText(PlayerProfile $playerProfile, Scripture $scripture): string
    {
        $unlockCondition = $scripture->unlock_condition ?? [];
        $requiredChapterId = (string) ($unlockCondition['clear_chapter_id'] ?? '');
        $requiredPlayerLevel = (int) ($unlockCondition['player_level'] ?? 1);
        $parts = [];

        if ($requiredChapterId !== '') {
            $chapterName = MainlineChapter::query()
                ->where('chapter_id', $requiredChapterId)
                ->value('chapter_name');
            $parts[] = sprintf('通关%s', $chapterName ?: $requiredChapterId);
        }

        if ((int) $playerProfile->level < $requiredPlayerLevel) {
            $parts[] = sprintf('达到 Lv.%d', $requiredPlayerLevel);
        }

        return $parts === [] ? '未解锁' : implode('，', $parts).'后解锁';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function nextUpgradeCosts(string $scriptureId, int $currentWorldLevel): array
    {
        return ScriptureUpgradeCost::query()
            ->where('scripture_id', $scriptureId)
            ->where('target_world_level', '>', $currentWorldLevel)
            ->orderBy('target_world_level')
            ->limit(1)
            ->get()
            ->map(static fn (ScriptureUpgradeCost $cost): array => [
                'target_world_level' => (int) $cost->target_world_level,
                'cost_items' => array_values($cost->cost_items ?? []),
                'cost_gold' => (int) $cost->cost_gold,
                'required_player_level' => (int) $cost->required_player_level,
            ])
            ->values()
            ->all();
    }

    private function resolveNextUpgradeCost(string $scriptureId, int $currentWorldLevel): ?ScriptureUpgradeCost
    {
        return ScriptureUpgradeCost::query()
            ->where('scripture_id', $scriptureId)
            ->where('target_world_level', '>', $currentWorldLevel)
            ->orderBy('target_world_level')
            ->first();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildBattleMonsterPayload(?ScriptureMonster $monster, ScriptureWorldTier $tier): ?array
    {
        if (! $monster instanceof ScriptureMonster) {
            return null;
        }

        return [
            'monster_id' => $monster->monster_id,
            'name' => $monster->name,
            'is_boss' => (bool) $monster->is_boss,
            'combat_role' => $monster->monster_type,
            'stats' => [
                'max_hp' => (int) round((int) $monster->base_hp * (float) $tier->hp_scale),
                'attack' => (int) round((int) $monster->base_atk * (float) $tier->atk_scale),
                'defense' => (int) round((int) $monster->base_def * (float) $tier->def_scale),
                'move_speed' => (float) $monster->move_speed,
                'attack_range' => $monster->is_boss ? 78.0 : 68.0,
                'attack_interval' => $monster->is_boss ? 1.45 : 1.7,
                'aggro_range' => $monster->is_boss ? 230.0 : 190.0,
            ],
            'skill_profile' => [
                'ai_type' => (string) ($monster->ai_type ?? ''),
                'skill_ids' => array_values($monster->skill_ids ?? []),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function selectDropTagItem(ScriptureDropTag $tag, int $battleSeed, int $tagIndex): ?array
    {
        $items = collect($tag->items ?? [])
            ->filter(static fn (mixed $entry): bool => is_array($entry) && filled($entry['item_id'] ?? null))
            ->values();

        if ($items->isEmpty()) {
            return null;
        }

        $totalWeight = max((int) $items->sum(static fn (array $entry): int => max((int) ($entry['weight'] ?? 0), 0)), 1);
        $sample = $this->hashToInt($battleSeed, $tag->drop_tag, 'weight', $tagIndex) % $totalWeight;
        $cursor = 0;

        foreach ($items as $entry) {
            $cursor += max((int) ($entry['weight'] ?? 0), 0);

            if ($sample < $cursor) {
                return $entry;
            }
        }

        return $items->last();
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function rollDropTagCount(array $entry, int $battleSeed, int $tagIndex): int
    {
        $min = max((int) ($entry['min'] ?? 1), 1);
        $max = max((int) ($entry['max'] ?? $min), $min);

        if ($min === $max) {
            return $min;
        }

        return $min + ($this->hashToInt($battleSeed, (string) ($entry['item_id'] ?? ''), 'count', $tagIndex) % ($max - $min + 1));
    }

    private function hashToInt(int $battleSeed, string $scope, string $type, int $index): int
    {
        $hash = hash('sha256', implode('|', [$battleSeed, $scope, $type, $index]));

        return (int) hexdec(substr($hash, 0, 8));
    }

    /**
     * @param  list<array<string, mixed>>  $rewards
     * @return list<array<string, mixed>>
     */
    private function mergeRewards(array $rewards): array
    {
        return collect($rewards)
            ->groupBy(static fn (array $entry): string => (string) ($entry['item_id'] ?? ''))
            ->map(static fn (Collection $entries, string $itemId): array => [
                'item_id' => $itemId,
                'count' => $entries->sum(static fn (array $entry): int => max((int) ($entry['count'] ?? 0), 0)),
            ])
            ->filter(static fn (array $entry): bool => $entry['item_id'] !== '' && $entry['count'] > 0)
            ->sortBy('item_id')
            ->values()
            ->all();
    }
}
