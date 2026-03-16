<?php

namespace App\Services;

use App\Enums\MonsterDropKind;
use App\Exceptions\ApiException;
use App\Models\BattleRecord;
use App\Models\DungeonDifficulty;
use App\Models\MainlineDifficulty;
use App\Models\Monster;
use App\Models\MonsterDrop;
use App\Models\PlayerProfile;
use App\Repositories\Contracts\BattleRuntimeRepositoryInterface;
use App\Repositories\Contracts\PlayerRuntimeRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BattleRuntimeService
{
    public function __construct(
        private readonly BattleRuntimeRepositoryInterface $battleRuntimeRepository,
        private readonly PlayerRuntimeRepositoryInterface $playerRuntimeRepository,
        private readonly PlayerRuntimeService $playerRuntimeService,
        private readonly StageRuntimeService $stageRuntimeService,
        private readonly DungeonRuntimeService $dungeonRuntimeService,
        private readonly ChallengeService $challengeService,
        private readonly ScriptureRuntimeService $scriptureRuntimeService,
        private readonly InventoryService $inventoryService,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function prepare(PlayerProfile $playerProfile, array $payload): array
    {
        if ((string) ($playerProfile->class_id ?? '') === '') {
            throw new ApiException('请先选择职业', 40032, 400);
        }

        $sourceType = (string) $payload['source_type'];
        $sourceId = (string) $payload['source_id'];
        $difficultyId = (string) ($payload['difficulty_id'] ?? '');
        $worldLevel = (int) ($payload['world_level'] ?? 0);
        $sourceContext = $this->resolveSourceContext($playerProfile, $sourceType, $sourceId, $difficultyId, $worldLevel);
        $playerSnapshot = $this->playerRuntimeService->buildPlayerSnapshot($playerProfile);
        $battleSeed = random_int(100000, 99999999);
        $enemyGroupSnapshot = $this->buildEnemyGroupSnapshot($playerProfile, $sourceType, $sourceId, $difficultyId, $worldLevel, $sourceContext);
        $battleRulesSnapshot = (array) ($sourceContext['battle_rules_snapshot'] ?? []);
        $battleId = (string) Str::uuid();
        $battleMapId = sprintf('map_%s_%s', $sourceType, $sourceId);

        $this->battleRuntimeRepository->create([
            'battle_id' => $battleId,
            'player_id' => (int) $playerProfile->player_id,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'difficulty_id' => $difficultyId,
            'world_level' => $worldLevel > 0 ? $worldLevel : null,
            'status' => 'prepared',
            'battle_map_id' => $battleMapId,
            'battle_seed' => $battleSeed,
            'request_snapshot' => $sourceContext,
            'player_snapshot' => $playerSnapshot,
            'enemy_group_snapshot' => $enemyGroupSnapshot,
        ]);

        $response = [
            'battle_id' => $battleId,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'battle_map_id' => $battleMapId,
            'battle_seed' => $battleSeed,
            'player_snapshot' => $playerSnapshot,
            'enemy_group_snapshot' => $enemyGroupSnapshot,
            'battle_rules_snapshot' => $battleRulesSnapshot,
        ];

        if ($sourceType === 'scripture') {
            $response['world_level'] = $worldLevel;
        } else {
            $response['difficulty_id'] = $difficultyId;
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function settle(PlayerProfile $playerProfile, array $payload): array
    {
        return DB::transaction(function () use ($playerProfile, $payload): array {
            $battleRecord = $this->battleRuntimeRepository->findByBattleIdForUpdate((string) $payload['battle_id']);

            if (! $battleRecord || (int) $battleRecord->player_id !== (int) $playerProfile->player_id) {
                throw new ApiException('battle_id 无效', 40441, 404);
            }

            if ($battleRecord->status === 'settled') {
                throw new ApiException('battle_id 已结算', 40941, 409);
            }

            $lockedProfile = $this->playerRuntimeRepository->getProfileForUpdate((int) $playerProfile->player_id);

            if (! $lockedProfile) {
                throw new ApiException('玩家不存在', 40442, 404);
            }

            $normalizedResult = $this->normalizeBattleResult((string) $payload['result']);
            $isVictory = $normalizedResult === 'victory';

            if ($battleRecord->source_type === 'dungeon') {
                $this->dungeonRuntimeService->assertDungeonSettlementAvailable(
                    $lockedProfile,
                    (string) ($battleRecord->request_snapshot['dungeon_id'] ?? ''),
                );
            }

            $normalRewards = $this->buildNormalRewards($battleRecord, $isVictory);
            $progressUpdate = $this->updateProgress($lockedProfile, $battleRecord, $isVictory, $payload);
            $firstClearRewards = $progressUpdate['first_clear_rewards'] ?? [];
            $weeklyRewards = $progressUpdate['weekly_rewards'] ?? [];
            unset($progressUpdate['first_clear_rewards']);
            unset($progressUpdate['weekly_rewards']);

            $allRewards = $this->mergeRewards(array_merge($normalRewards, $firstClearRewards, $weeklyRewards));
            $rewardApplyResult = $this->inventoryService->applyRewards($lockedProfile, $allRewards);
            $updatedProfile = $rewardApplyResult['player_profile'];
            $initPayload = $this->playerRuntimeService->getInitPayload($updatedProfile);

            $battleRecord = $this->battleRuntimeRepository->update($battleRecord, [
                'status' => 'settled',
                'result' => $normalizedResult,
                'duration' => max((int) round((float) $payload['duration']), 0),
                'cleared_wave' => max((int) $payload['cleared_wave'], 0),
                'settle_payload' => array_merge(
                    (array) ($payload['client_summary'] ?? []),
                    ['weekly_rewards' => $weeklyRewards],
                ),
                'rewards' => $normalRewards,
                'first_clear_rewards' => $firstClearRewards,
                'settled_at' => Carbon::now(),
            ]);

            return [
                'battle_id' => $battleRecord->battle_id,
                'result' => $normalizedResult,
                'rewards' => $normalRewards,
                'first_clear_rewards' => $firstClearRewards,
                'weekly_rewards' => $weeklyRewards,
                'all_rewards' => $allRewards,
                'progress_update' => $progressUpdate,
                'inventory_update' => [
                    'items' => $initPayload['inventory'],
                    'changed_items' => $rewardApplyResult['item_rewards'],
                ],
                'currencies_update' => [
                    'gold' => (int) $updatedProfile->gold,
                    'jade' => (int) $updatedProfile->jade,
                    'contribution' => (int) $updatedProfile->contribution,
                    'delta' => $rewardApplyResult['currency_delta'],
                ],
                'player' => $initPayload['player'],
                'stage_progress' => $initPayload['stage_progress'],
                'dungeon_progress' => $initPayload['dungeon_progress'],
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveSourceContext(PlayerProfile $playerProfile, string $sourceType, string $sourceId, string $difficultyId, int $worldLevel): array
    {
        if ($sourceType === 'stage') {
            $sourceContext = $this->stageRuntimeService->assertStageAccess($playerProfile, $sourceId, $difficultyId);
            /** @var MainlineDifficulty $difficulty */
            $difficulty = $sourceContext['difficulty'];

            return [
                'source_type' => 'stage',
                'chapter_id' => $sourceContext['chapter']->chapter_id,
                'chapter_name' => $sourceContext['chapter']->chapter_name,
                'node_id' => $sourceContext['node']->node_id,
                'node_name' => $sourceContext['node']->node_name,
                'difficulty_id' => $difficulty->difficulty_id,
                'recommended_power' => (int) $difficulty->recommended_power,
                'first_clear_reward_group_id' => $difficulty->first_clear_reward_group_id,
            ];
        }

        if ($sourceType === 'dungeon') {
            $sourceContext = $this->dungeonRuntimeService->assertDungeonAccess($playerProfile, $sourceId, $difficultyId);
            /** @var DungeonDifficulty $difficulty */
            $difficulty = $sourceContext['difficulty'];

            return [
                'source_type' => 'dungeon',
                'dungeon_id' => $sourceContext['dungeon']->dungeon_id,
                'dungeon_name' => $sourceContext['dungeon']->dungeon_name,
                'difficulty_id' => $difficulty->difficulty_id,
                'recommended_power' => (int) $difficulty->recommended_power,
                'first_clear_reward_group_id' => $difficulty->first_clear_reward_group_id,
                'dungeon_spawn_rule' => [
                    'normal_monster_ids' => $difficulty->normal_monster_ids ?? [],
                    'elite_monster_ids' => $difficulty->elite_monster_ids ?? [],
                    'boss_monster_id' => $difficulty->boss_monster_id,
                    'normal_spawn_interval' => (int) ($difficulty->normal_spawn_interval ?? 3),
                    'normal_spawn_count' => (int) ($difficulty->normal_spawn_count ?? 2),
                    'normal_alive_limit' => (int) ($difficulty->normal_alive_limit ?? 6),
                    'elite_spawn_interval' => (int) ($difficulty->elite_spawn_interval ?? 6),
                    'elite_spawn_count' => (int) ($difficulty->elite_spawn_count ?? 1),
                    'elite_alive_limit' => (int) ($difficulty->elite_alive_limit ?? 1),
                    'normal_kill_to_spawn_elite' => (int) ($difficulty->normal_kill_to_spawn_elite ?? 12),
                    'elite_kill_to_spawn_boss' => (int) ($difficulty->elite_kill_to_spawn_boss ?? 3),
                    'stop_spawn_after_boss_appears' => (bool) ($difficulty->stop_spawn_after_boss_appears ?? true),
                    'clear_on_boss_killed' => (bool) ($difficulty->clear_on_boss_killed ?? true),
                ],
            ];
        }

        if ($sourceType === 'challenge') {
            $sourceContext = $this->challengeService->assertAccess($playerProfile, $sourceId, $difficultyId);
            $floor = $sourceContext['floor'];

            return [
                'source_type' => 'challenge',
                'challenge_id' => $sourceContext['challenge']->challenge_id,
                'challenge_name' => $sourceContext['challenge']->challenge_name,
                'floor_id' => (string) ($floor['floor_id'] ?? $difficultyId),
                'floor_name' => (string) ($floor['floor_name'] ?? $difficultyId),
                'difficulty_id' => (string) ($floor['floor_id'] ?? $difficultyId),
                'floor' => (int) ($floor['floor'] ?? 1),
                'recommended_power' => (int) ($floor['recommended_power'] ?? 0),
                'first_clear_reward_group_id' => (string) ($floor['first_clear_reward_group_id'] ?? ''),
                'weekly_reward_group_id' => (string) ($floor['weekly_reward_group_id'] ?? ''),
                'normal_reward_group_id' => (string) ($floor['normal_reward_group_id'] ?? ''),
            ];
        }

        if ($sourceType === 'scripture') {
            $sourceContext = $this->scriptureRuntimeService->assertScriptureBattleAccess($playerProfile, $sourceId, $worldLevel);
            $scripture = $sourceContext['scripture'];
            $tier = $sourceContext['tier'];

            return [
                'source_type' => 'scripture',
                'scripture_id' => $scripture->scripture_id,
                'scripture_name' => $scripture->scripture_name,
                'scripture_group' => $scripture->scripture_group,
                'world_level' => $worldLevel,
                'current_world_level' => (int) $sourceContext['current_world_level'],
                'max_unlocked_world_level' => (int) $sourceContext['max_unlocked_world_level'],
                'normal_monster_ids' => array_values($tier->normal_monster_ids ?? []),
                'elite_monster_ids' => array_values($tier->elite_monster_ids ?? []),
                'boss_monster_ids' => array_values($tier->boss_monster_ids ?? []),
                'extra_drop_tags' => array_values($tier->extra_drop_tags ?? []),
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

        throw new ApiException('参数错误', 42201, 422);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEnemyGroupSnapshot(PlayerProfile $playerProfile, string $sourceType, string $sourceId, string $difficultyId, int $worldLevel, array $sourceContext = []): array
    {
        if ($sourceType === 'scripture') {
            return $this->scriptureRuntimeService
                ->buildBattlePayload((string) ($sourceContext['scripture_id'] ?? $sourceId), $worldLevel)['enemy_group_snapshot'];
        }

        $encounter = $this->resolveEncounterDefinition($sourceType, $sourceId, $difficultyId);
        $monsterIds = $encounter['monster_ids'];
        $difficultyMultiplier = $this->difficultyMultiplier($difficultyId);
        $monsterMap = Monster::query()
            ->whereIn('monster_id', $monsterIds)
            ->get()
            ->mapWithKeys(static fn (Monster $monster): array => [
                $monster->monster_id => $monster,
            ]);
        $monsters = [];

        foreach ($monsterIds as $monsterId) {
            /** @var Monster|null $monster */
            $monster = $monsterMap->get($monsterId);

            if (! $monster) {
                continue;
            }

            $monsters[] = [
                'monster_id' => $monster->monster_id,
                'name' => $monster->name,
                'is_boss' => (bool) $monster->is_boss,
                'combat_role' => (string) ($monster->combat_role ?? ''),
                'stats' => $this->buildMonsterCombatStats($monster, $difficultyMultiplier),
                'skill_profile' => $this->buildBossSkillProfile($monster),
            ];
        }

        $snapshot = [
            'monster_group_id' => (string) ($encounter['monster_group_id'] ?? sprintf('%s_%s_%s', $sourceType, $sourceId, $difficultyId)),
            'monsters' => $monsters,
        ];

        // 如果是副本，添加刷怪规则
        if ($sourceType === 'dungeon') {
            $spawnRules = $this->dungeonRuntimeService->getDungeonSpawnRules(
                $playerProfile,
                $sourceId,
                $difficultyId
            );
            $snapshot['spawn_rules'] = $spawnRules;
        }

        return $snapshot;
    }

    /**
     * @return array{monster_group_id:string,monster_ids:list<string>}
     */
    private function resolveEncounterDefinition(string $sourceType, string $sourceId, string $difficultyId): array
    {
        if ($sourceType === 'challenge') {
            return $this->challengeService->resolveEncounterDefinition($sourceId, $difficultyId);
        }

        $encounterConfig = config(sprintf('game_runtime.encounters.%s', $sourceType), []);

        if (isset($encounterConfig[$sourceId])) {
            $sourceEncounter = $encounterConfig[$sourceId];

            if (is_array($sourceEncounter) && isset($sourceEncounter[$difficultyId]) && is_array($sourceEncounter[$difficultyId])) {
                $monsterIds = array_values(array_filter(
                    $sourceEncounter[$difficultyId]['monster_ids'] ?? $sourceEncounter[$difficultyId],
                    static fn ($monsterId): bool => (string) $monsterId !== '',
                ));

                if ($monsterIds !== []) {
                    return [
                        'monster_group_id' => (string) ($sourceEncounter[$difficultyId]['monster_group_id'] ?? sprintf('%s_%s_%s', $sourceType, $sourceId, $difficultyId)),
                        'monster_ids' => $monsterIds,
                    ];
                }
            }

            if (is_array($sourceEncounter) && isset($sourceEncounter['default'])) {
                $monsterIds = array_values(array_filter(
                    $sourceEncounter['default']['monster_ids'] ?? $sourceEncounter['default'],
                    static fn ($monsterId): bool => (string) $monsterId !== '',
                ));

                if ($monsterIds !== []) {
                    return [
                        'monster_group_id' => (string) ($sourceEncounter['default']['monster_group_id'] ?? sprintf('%s_%s_%s', $sourceType, $sourceId, $difficultyId)),
                        'monster_ids' => $monsterIds,
                    ];
                }
            }

            if (is_array($sourceEncounter)) {
                $monsterIds = array_values(array_filter($sourceEncounter, static fn ($monsterId): bool => is_string($monsterId) && $monsterId !== ''));

                if ($monsterIds !== []) {
                    return [
                        'monster_group_id' => sprintf('%s_%s_%s', $sourceType, $sourceId, $difficultyId),
                        'monster_ids' => $monsterIds,
                    ];
                }
            }
        }

        return [
            'monster_group_id' => sprintf('%s_%s_%s', $sourceType, $sourceId, $difficultyId),
            'monster_ids' => Monster::query()
                ->orderBy('monster_id')
                ->limit(3)
                ->pluck('monster_id')
                ->all(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rollMonsterDrops(BattleRecord $battleRecord): array
    {
        $enemyGroupSnapshot = $battleRecord->enemy_group_snapshot ?? [];
        $monsters = $enemyGroupSnapshot['monsters'] ?? [];
        $monsterIds = array_values(array_filter(array_map(
            static fn (array $monster): string => (string) ($monster['monster_id'] ?? ''),
            is_array($monsters) ? $monsters : [],
        )));
        $dropDefinitions = MonsterDrop::query()
            ->whereIn('monster_id', $monsterIds)
            ->orderBy('monster_id')
            ->orderByRaw("CASE drop_kind WHEN 'boss_fixed' THEN 0 WHEN 'boss_core' THEN 1 ELSE 2 END")
            ->orderBy('item_id')
            ->get();
        $rewards = [];

        foreach ($dropDefinitions as $dropDefinition) {
            $dropRate = (float) $dropDefinition->drop_rate;
            $dropKind = (string) $dropDefinition->drop_kind;
            $guaranteed = $dropKind === MonsterDropKind::BossFixed->value || $dropRate >= 0.999;

            if ($guaranteed || $this->deterministicRoll($battleRecord->battle_seed, $dropDefinition->monster_id, $dropDefinition->item_id) <= $dropRate) {
                $rewards[] = [
                    'item_id' => $dropDefinition->item_id,
                    'count' => 1,
                ];
            }
        }

        return $this->mergeRewards($rewards);
    }

    /**
     * @return array<string, mixed>
     */
    private function updateProgress(PlayerProfile $playerProfile, BattleRecord $battleRecord, bool $isVictory, array $payload = []): array
    {
        if ($battleRecord->source_type === 'stage') {
            $chapterId = (string) ($battleRecord->request_snapshot['chapter_id'] ?? '');
            $nodeId = (string) ($battleRecord->request_snapshot['node_id'] ?? '');
            $difficultyId = (string) $battleRecord->difficulty_id;
            $existing = $this->playerRuntimeRepository->findStageProgress($playerProfile->player_id, $nodeId, $difficultyId);
            $isFirstClearNow = $isVictory && ! (bool) ($existing?->is_first_clear ?? false);
            $progress = $existing;

            if ($isVictory) {
                $progress = $this->playerRuntimeRepository->upsertStageProgress(
                    [
                        'player_id' => $playerProfile->player_id,
                        'chapter_id' => $chapterId,
                        'node_id' => $nodeId,
                        'difficulty_id' => $difficultyId,
                    ],
                    [
                        'is_first_clear' => true,
                        'clear_count' => (int) ($existing?->clear_count ?? 0) + 1,
                    ],
                );

                $nextLocation = $this->stageRuntimeService->resolveNextCurrentLocation($playerProfile, $nodeId, $difficultyId);

                $this->playerRuntimeRepository->updateProfile($playerProfile, $nextLocation);
            }

            return [
                'source_type' => 'stage',
                'chapter_id' => $chapterId,
                'node_id' => $nodeId,
                'difficulty_id' => $difficultyId,
                'is_first_clear_now' => $isFirstClearNow,
                'clear_count' => (int) ($progress?->clear_count ?? $existing?->clear_count ?? 0),
                'first_clear_rewards' => $isFirstClearNow
                    ? $this->resolveRewardGroupItems((string) ($battleRecord->request_snapshot['first_clear_reward_group_id'] ?? ''))
                    : [],
                'weekly_rewards' => [],
            ];
        }

        if ($battleRecord->source_type === 'challenge') {
            return $this->challengeService->settleChallenge($playerProfile, $battleRecord, $isVictory);
        }

        if ($battleRecord->source_type === 'scripture') {
            $progressState = $this->scriptureRuntimeService->getProgressState(
                $playerProfile,
                (string) ($battleRecord->request_snapshot['scripture_id'] ?? $battleRecord->source_id),
            );

            return [
                'source_type' => 'scripture',
                'scripture_id' => (string) ($battleRecord->request_snapshot['scripture_id'] ?? $battleRecord->source_id),
                'world_level' => (int) ($battleRecord->world_level ?? $battleRecord->request_snapshot['world_level'] ?? 0),
                'current_world_level' => (int) $progressState['current_world_level'],
                'max_unlocked_world_level' => (int) $progressState['max_unlocked_world_level'],
                'first_clear_rewards' => [],
                'weekly_rewards' => [],
            ];
        }

        $dungeonId = (string) ($battleRecord->request_snapshot['dungeon_id'] ?? '');
        $difficultyId = (string) $battleRecord->difficulty_id;
        $existing = $this->playerRuntimeRepository->findDungeonProgress($playerProfile->player_id, $dungeonId, $difficultyId);
        $isFirstClearNow = $isVictory && ! (bool) ($existing?->is_first_clear ?? false);
        $clearCount = (int) ($existing?->clear_count ?? 0) + ($isVictory ? 1 : 0);
        $dailyCount = (int) ($existing?->daily_count ?? 0) + 1;
        
        // 检查是否击杀了Boss
        $spawnRule = $battleRecord->request_snapshot['dungeon_spawn_rule'] ?? [];
        $bossMonsterId = $spawnRule['boss_monster_id'] ?? '';
        $clearOnBossKilled = $spawnRule['clear_on_boss_killed'] ?? true;
        
        $bossKilled = false;
        if ($isVictory && $bossMonsterId && $clearOnBossKilled) {
            // 检查战斗记录中是否包含Boss击杀信息
            $clientSummary = $payload['client_summary'] ?? [];
            $killedMonsters = $clientSummary['killed_monsters'] ?? [];
            $bossKilled = in_array($bossMonsterId, $killedMonsters);
        }
        
        // 如果Boss被击杀且配置为Boss击杀后通关，则强制胜利
        if ($bossKilled) {
            $isVictory = true;
            $isFirstClearNow = $isFirstClearNow || ! (bool) ($existing?->is_first_clear ?? false);
            $clearCount = (int) ($existing?->clear_count ?? 0) + 1;
        }
        
        $progress = $this->playerRuntimeRepository->upsertDungeonProgress(
            [
                'player_id' => $playerProfile->player_id,
                'dungeon_id' => $dungeonId,
                'difficulty_id' => $difficultyId,
            ],
            [
                'is_first_clear' => $isVictory ? true : (bool) ($existing?->is_first_clear ?? false),
                'clear_count' => $clearCount,
                'daily_count' => $dailyCount,
                'daily_reset_at' => Carbon::now()->startOfDay(),
            ],
        );

        return [
            'source_type' => 'dungeon',
            'dungeon_id' => $dungeonId,
            'difficulty_id' => $difficultyId,
            'is_first_clear_now' => $isFirstClearNow,
            'clear_count' => (int) $progress->clear_count,
            'daily_count' => (int) $progress->daily_count,
            'boss_killed' => $bossKilled,
            'first_clear_rewards' => $isFirstClearNow
                ? $this->resolveRewardGroupItems((string) ($battleRecord->request_snapshot['first_clear_reward_group_id'] ?? ''))
                : [],
            'weekly_rewards' => [],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildNormalRewards(BattleRecord $battleRecord, bool $isVictory): array
    {
        if (! $isVictory) {
            return [];
        }

        if ($battleRecord->source_type === 'challenge') {
            return $this->mergeRewards(array_merge(
                $this->rollMonsterDrops($battleRecord),
                $this->challengeService->resolveNormalRewards(
                    (string) ($battleRecord->request_snapshot['challenge_id'] ?? ''),
                    (string) $battleRecord->difficulty_id,
                ),
            ));
        }

        if ($battleRecord->source_type === 'scripture') {
            return $this->scriptureRuntimeService->resolveBattleRewards(
                (string) ($battleRecord->request_snapshot['scripture_id'] ?? $battleRecord->source_id),
                (int) ($battleRecord->world_level ?? $battleRecord->request_snapshot['world_level'] ?? 0),
                (int) $battleRecord->battle_seed,
            );
        }

        return $this->rollMonsterDrops($battleRecord);
    }

    /**
     * @param  list<array<string, mixed>>  $rewards
     * @return list<array<string, mixed>>
     */
    private function mergeRewards(array $rewards): array
    {
        $counts = [];

        foreach ($rewards as $reward) {
            $itemId = (string) ($reward['item_id'] ?? '');
            $count = max((int) ($reward['count'] ?? 0), 0);

            if ($itemId === '' || $count <= 0) {
                continue;
            }

            $counts[$itemId] = ($counts[$itemId] ?? 0) + $count;
        }

        ksort($counts);

        return collect($counts)
            ->map(static fn (int $count, string $itemId): array => [
                'item_id' => $itemId,
                'count' => $count,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function resolveRewardGroupItems(string $rewardGroupId): array
    {
        $rewardGroups = config('game_runtime.reward_groups', []);
        $items = $rewardGroups[$rewardGroupId] ?? [];

        if (! is_array($items)) {
            return [];
        }

        return $this->mergeRewards(array_map(
            static fn (array $entry): array => [
                'item_id' => (string) ($entry['item_id'] ?? ''),
                'count' => max((int) ($entry['count'] ?? 0), 0),
            ],
            $items,
        ));
    }

    private function difficultyMultiplier(string $difficultyId): float
    {
        return match ($difficultyId) {
            'easy' => 1.0,
            'normal' => 1.35,
            'hard' => 1.75,
            'nightmare' => 2.15,
            'epic' => 2.55,
            default => 1.0,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildBossSkillProfile(Monster $monster): array
    {
        $behaviorProfile = $monster->behavior_profile ?? [];

        if (is_array($behaviorProfile) && $behaviorProfile !== []) {
            return $behaviorProfile;
        }

        if (! $monster->is_boss) {
            return [];
        }

        if ($monster->monster_id === 'mon_new_boss') {
            return [
                'name' => '雷狱震落',
                'cooldown' => 5.5,
                'burst_ratio' => 0.4,
                'control_name' => '雷缚',
                'control_duration' => 1.6,
                'dot_name' => '感电',
                'dot_ratio' => 0.24,
                'dot_duration' => 4.0,
                'self_hot_name' => '雷兽回潮',
                'self_hot_ratio' => 0.08,
            ];
        }

        return [
            'name' => '狐火震慑',
            'cooldown' => 6.0,
            'burst_ratio' => 0.22,
            'control_name' => '震慑',
            'control_duration' => 1.2,
            'dot_name' => '妖火',
            'dot_ratio' => 0.2,
            'dot_duration' => 4.0,
        ];
    }

    /**
     * @return array<string, int|float>
     */
    private function buildMonsterCombatStats(Monster $monster, float $difficultyMultiplier): array
    {
        $profile = $monster->behavior_profile ?? [];
        $hpRatio = (float) ($profile['hp_ratio'] ?? 1.0);
        $attackRatio = (float) ($profile['attack_ratio'] ?? 1.0);

        return [
            'max_hp' => (int) round((int) $monster->base_hp * $difficultyMultiplier * $hpRatio),
            'attack' => (int) round((int) $monster->base_atk * (0.88 + $difficultyMultiplier * 0.12) * $attackRatio),
            'defense' => (int) round((float) ($profile['base_defense'] ?? 10) * $difficultyMultiplier),
            'move_speed' => (float) ($profile['move_speed'] ?? ($monster->is_boss ? 130.0 : 118.0)),
            'attack_range' => (float) ($profile['attack_range'] ?? ($monster->is_boss ? 78.0 : 68.0)),
            'attack_interval' => (float) ($profile['attack_interval'] ?? ($monster->is_boss ? 1.45 : 1.7)),
            'aggro_range' => (float) ($profile['aggro_range'] ?? ($monster->is_boss ? 230.0 : 190.0)),
        ];
    }

    private function deterministicRoll(int $battleSeed, string $monsterId, string $itemId): float
    {
        $hash = hash('sha256', implode('|', [$battleSeed, $monsterId, $itemId]));
        $sample = hexdec(substr($hash, 0, 8));

        return $sample / 0xFFFFFFFF;
    }

    private function normalizeBattleResult(string $result): string
    {
        $normalized = strtolower(trim($result));

        return match ($normalized) {
            'win', 'clear', 'success', 'victory' => 'victory',
            default => 'defeat',
        };
    }
}
