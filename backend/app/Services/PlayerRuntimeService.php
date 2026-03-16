<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\BlueAffix;
use App\Models\CharacterClass;
use App\Models\Equipment;
use App\Models\EquipmentSet;
use App\Models\Gem;
use App\Models\PlayerProfile;
use App\Models\PurpleRefinement;
use App\Models\Skill;
use App\Repositories\Contracts\PlayerRuntimeRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlayerRuntimeService
{
    public function __construct(
        private readonly PlayerRuntimeRepositoryInterface $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function login(array $payload): array
    {
        return DB::transaction(function () use ($payload): array {
            $requestedPlayerId = max((int) ($payload['player_id'] ?? 0), 0);
            $playerId = $requestedPlayerId > 0 ? $requestedPlayerId : $this->repository->nextPlayerId();
            $nickname = trim((string) ($payload['nickname'] ?? ''));
            $playerProfile = $this->repository->findByPlayerId($playerId);
            $isNewPlayer = false;

            if (! $playerProfile) {
                $isNewPlayer = true;
                $playerProfile = $this->repository->createProfile($this->buildStarterProfile($playerId, $nickname));
                $this->repository->syncInventory($playerId, $this->buildStarterInventoryRows($playerId));
            }

            $playerProfile = $this->repository->updateProfile($playerProfile, [
                'nickname' => $nickname !== '' ? $nickname : $playerProfile->nickname,
                'auth_token' => Str::random(60),
                'last_login_at' => Carbon::now(),
            ]);

            $playerProfile = $this->syncComputedFields($playerProfile);

            return [
                'token' => $playerProfile->auth_token,
                'player_id' => (int) $playerProfile->player_id,
                'nickname' => $playerProfile->nickname,
                'class_id' => $playerProfile->class_id,
                'is_new_player' => $isNewPlayer,
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function getInitPayload(PlayerProfile $playerProfile): array
    {
        $playerProfile = $this->syncComputedFields($playerProfile);
        $inventory = $this->buildInventoryPayload($playerProfile->player_id);
        $stageProgress = $this->buildStageProgressPayload($playerProfile->player_id);
        $dungeonProgress = $this->buildDungeonProgressPayload($playerProfile->player_id);

        return [
            'player' => $this->buildPlayerPayload($playerProfile, $inventory),
            'inventory' => $inventory,
            'stage_progress' => $stageProgress,
            'dungeon_progress' => $dungeonProgress,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function selectClass(PlayerProfile $playerProfile, string $classId): array
    {
        $characterClass = CharacterClass::query()
            ->where('class_id', $classId)
            ->first();

        if (! $characterClass) {
            throw new ApiException('职业不存在', 40411, 404);
        }

        if (! $characterClass->is_open) {
            throw new ApiException('职业未开放', 40031, 400);
        }

        $skillLevels = $this->primeSkillLevelsForClass($playerProfile->skill_levels ?? [], $classId);

        $playerProfile = $this->repository->updateProfile($playerProfile, [
            'class_id' => $classId,
            'skill_levels' => $skillLevels,
        ]);

        return $this->getInitPayload($playerProfile);
    }

    /**
     * @return array{items:list<array<string, mixed>>,currencies:array<string, int>}
     */
    public function getInventoryPayload(PlayerProfile $playerProfile): array
    {
        $playerProfile = $this->repository->refreshProfile($playerProfile);

        return [
            'items' => $this->buildInventoryPayload($playerProfile->player_id),
            'currencies' => [
                'gold' => (int) $playerProfile->gold,
                'jade' => (int) $playerProfile->jade,
                'contribution' => (int) $playerProfile->contribution,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildPlayerSnapshot(PlayerProfile $playerProfile): array
    {
        $playerProfile = $this->syncComputedFields($playerProfile);
        $activeSkills = $this->getRuntimeSkillsForPlayer($playerProfile, 'active');
        $passiveSkills = $this->getRuntimeSkillsForPlayer($playerProfile, 'passive');

        return [
            'player_id' => (int) $playerProfile->player_id,
            'class_id' => (string) $playerProfile->class_id,
            'level' => (int) $playerProfile->level,
            'resource_name' => $this->getResourceName((string) $playerProfile->class_id),
            'max_energy' => (int) $playerProfile->max_energy,
            'class_profile' => $this->getClassCombatProfile((string) $playerProfile->class_id),
            'stats' => $this->calculateTotalStats($playerProfile),
            'skills' => [
                'active' => $activeSkills,
                'passive' => $passiveSkills,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getRuntimeSkillsForPlayer(PlayerProfile $playerProfile, string $typeFilter = ''): array
    {
        $classId = (string) ($playerProfile->class_id ?? '');

        if ($classId === '') {
            return [];
        }

        $skillLevels = $playerProfile->skill_levels ?? [];

        return Skill::query()
            ->where('class_id', $classId)
            ->where('is_open', true)
            ->orderByRaw("CASE type WHEN 'active' THEN 0 ELSE 1 END")
            ->orderBy('unlock_level')
            ->orderBy('skill_id')
            ->get()
            ->filter(function (Skill $skill) use ($playerProfile, $typeFilter): bool {
                if ($typeFilter !== '' && $skill->type !== $typeFilter) {
                    return false;
                }

                return (int) $skill->unlock_level <= (int) $playerProfile->level;
            })
            ->map(function (Skill $skill) use ($skillLevels): array {
                $skillLevel = max((int) ($skillLevels[$skill->skill_id] ?? 1), 1);

                return [
                    'skill_id' => $skill->skill_id,
                    'class_id' => $skill->class_id,
                    'skill_name' => $skill->skill_name,
                    'skill_desc' => $skill->skill_desc,
                    'type' => $skill->type,
                    'effect_type' => $skill->effect_type,
                    'effect' => $skill->effect_type,
                    'target_type' => $skill->target_type,
                    'range' => $skill->target_type,
                    'cooldown' => (int) $skill->cooldown,
                    'cost' => (int) $skill->cost,
                    'unlock_level' => (int) $skill->unlock_level,
                    'max_level' => (int) $skill->max_level,
                    'power_base' => (int) $skill->power_base,
                    'damage' => (int) $skill->power_base,
                    'power_per_level' => (int) $skill->power_per_level,
                    'duration' => (int) $skill->duration,
                    'chance' => (float) $skill->chance,
                    'stat_bonuses' => $skill->stat_bonuses ?? [],
                    'effect_payload' => $skill->effect_payload ?? [],
                    'is_open' => (bool) $skill->is_open,
                    'skill_level' => $skillLevel,
                    'scaled_power' => (int) $skill->power_base + max($skillLevel - 1, 0) * (int) $skill->power_per_level,
                ];
            })
            ->values()
            ->all();
    }

    public function syncComputedFields(PlayerProfile $playerProfile): PlayerProfile
    {
        $skillLevels = $playerProfile->skill_levels ?? [];
        $classId = (string) ($playerProfile->class_id ?? '');

        if ($classId !== '') {
            $skillLevels = $this->primeSkillLevelsForClass($skillLevels, $classId);
        }

        $stats = $this->calculateTotalStats($playerProfile, $skillLevels);
        $updates = [
            'skill_levels' => $skillLevels,
            'power' => (int) $stats['power'],
        ];

        if ($updates['power'] === (int) $playerProfile->power && $skillLevels === ($playerProfile->skill_levels ?? [])) {
            return $this->repository->refreshProfile($playerProfile);
        }

        return $this->repository->updateProfile($playerProfile, $updates);
    }

    /**
     * @param  array<string, mixed>|null  $skillLevelsOverride
     * @return array<string, int|float>
     */
    public function calculateTotalStats(PlayerProfile $playerProfile, ?array $skillLevelsOverride = null): array
    {
        $baseAtk = 28 + ((int) $playerProfile->level - 1) * 4;
        $baseDef = 18 + ((int) $playerProfile->level - 1) * 3;
        $bonusHp = 0;
        $bonusBossDmg = 0;
        $bonusAttackSpeed = 0.0;
        $bonusDamageRatio = 0.0;
        $classBonuses = $this->getClassStatBonuses((string) ($playerProfile->class_id ?? ''));
        $equipmentSummary = $playerProfile->equipment_summary ?? [];

        $baseAtk += (int) ($classBonuses['bonus_atk'] ?? 0);
        $baseDef += (int) ($classBonuses['bonus_def'] ?? 0);
        $bonusHp += (int) ($classBonuses['bonus_hp'] ?? 0);
        $bonusBossDmg += (int) ($classBonuses['bonus_boss_dmg'] ?? 0);
        $bonusAttackSpeed += (float) ($classBonuses['bonus_attack_speed'] ?? 0.0);
        $bonusDamageRatio += (float) ($classBonuses['bonus_damage_ratio'] ?? 0.0);

        $equipIds = array_values($equipmentSummary['equip_ids'] ?? []);
        $gems = array_values($equipmentSummary['equipped_gem_ids'] ?? []);
        $blueAffixes = array_values($equipmentSummary['blue_affix_ids'] ?? []);
        $purpleRefinements = array_values($equipmentSummary['purple_refinement_ids'] ?? []);
        $setCounts = array_values($equipmentSummary['set_counts'] ?? []);
        $skillLevels = $skillLevelsOverride ?? ($playerProfile->skill_levels ?? []);

        foreach (Equipment::query()->whereIn('equip_id', $equipIds)->get() as $equipment) {
            $baseAtk += (int) $equipment->base_atk;
            $baseDef += (int) $equipment->base_def;
        }

        foreach (Gem::query()->whereIn('gem_id', $gems)->get() as $gem) {
            $baseAtk += (int) $gem->bonus_atk;
            $bonusBossDmg += (int) $gem->bonus_boss_dmg;
        }

        foreach (BlueAffix::query()->whereIn('affix_id', $blueAffixes)->get() as $blueAffix) {
            $bonuses = $blueAffix->bonuses ?? [];
            $baseAtk += (int) ($bonuses['bonus_atk'] ?? 0);
            $baseDef += (int) ($bonuses['bonus_def'] ?? 0);
            $bonusHp += (int) ($bonuses['bonus_hp'] ?? 0);
            $bonusAttackSpeed += (float) ($bonuses['bonus_attack_speed'] ?? 0.0);
            $bonusDamageRatio += (float) ($bonuses['bonus_damage_ratio'] ?? 0.0);
        }

        foreach (PurpleRefinement::query()->whereIn('refinement_id', $purpleRefinements)->get() as $purpleRefinement) {
            $bonuses = $purpleRefinement->bonuses ?? [];
            $baseAtk += (int) ($bonuses['bonus_atk'] ?? 0);
            $baseDef += (int) ($bonuses['bonus_def'] ?? 0);
            $bonusHp += (int) ($bonuses['bonus_hp'] ?? 0);
            $bonusBossDmg += (int) ($bonuses['bonus_boss_dmg'] ?? 0);
            $bonusAttackSpeed += (float) ($bonuses['bonus_attack_speed'] ?? 0.0);
            $bonusDamageRatio += (float) ($bonuses['bonus_damage_ratio'] ?? 0.0);
        }

        $setEffectMap = EquipmentSet::query()
            ->whereIn('set_id', array_map(static fn (array $entry): string => (string) ($entry['set_id'] ?? ''), $setCounts))
            ->get()
            ->mapWithKeys(static fn (EquipmentSet $equipmentSet): array => [
                $equipmentSet->set_id => $equipmentSet,
            ]);

        foreach ($setCounts as $setCount) {
            $setId = (string) ($setCount['set_id'] ?? '');
            $equippedCount = (int) ($setCount['equipped_count'] ?? 0);
            $equipmentSet = $setEffectMap->get($setId);

            if (! $equipmentSet) {
                continue;
            }

            foreach ($equipmentSet->effects ?? [] as $effect) {
                if ($equippedCount < (int) ($effect['count'] ?? 0)) {
                    continue;
                }

                $baseAtk += (int) ($effect['bonus_atk'] ?? 0);
                $baseDef += (int) ($effect['bonus_def'] ?? 0);
                $bonusHp += (int) ($effect['bonus_hp'] ?? 0);
                $bonusBossDmg += (int) ($effect['bonus_boss_dmg'] ?? 0);
                $bonusAttackSpeed += (float) ($effect['bonus_attack_speed'] ?? 0.0);
                $bonusDamageRatio += (float) ($effect['bonus_damage_ratio'] ?? 0.0);
            }
        }

        foreach ($this->getRuntimeSkillsForPlayer($playerProfile, 'passive') as $skill) {
            $skillId = (string) $skill['skill_id'];
            $skillLevel = max((int) ($skillLevels[$skillId] ?? $skill['skill_level'] ?? 1), 1);
            $bonuses = $skill['stat_bonuses'] ?? [];
            $baseAtk += (int) ($bonuses['bonus_atk'] ?? 0) * $skillLevel;
            $baseDef += (int) ($bonuses['bonus_def'] ?? 0) * $skillLevel;
            $bonusHp += (int) ($bonuses['bonus_hp'] ?? 0) * $skillLevel;
            $bonusBossDmg += (int) ($bonuses['bonus_boss_dmg'] ?? 0) * $skillLevel;
            $bonusAttackSpeed += (float) ($bonuses['bonus_attack_speed'] ?? 0.0) * $skillLevel;
            $bonusDamageRatio += (float) ($bonuses['bonus_damage_ratio'] ?? 0.0) * $skillLevel;
        }

        $maxHp = max((int) $playerProfile->max_hp + $bonusHp, 1);

        return [
            'atk' => $baseAtk,
            'def' => $baseDef,
            'max_hp' => $maxHp,
            'boss_dmg' => $bonusBossDmg,
            'attack_speed_bonus' => $bonusAttackSpeed,
            'damage_ratio_bonus' => $bonusDamageRatio,
            'power' => (int) round(
                $baseAtk * 2.2
                + $baseDef * 1.8
                + $maxHp * 0.2
                + $bonusBossDmg * 3.0
                + $bonusAttackSpeed * 80.0
                + $bonusDamageRatio * 120.0,
            ),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function buildInventoryPayload(int $playerId): array
    {
        return $this->repository->getInventory($playerId)
            ->map(static fn ($item): array => [
                'item_id' => $item->item_id,
                'count' => (int) $item->count,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function buildStageProgressPayload(int $playerId): array
    {
        return $this->repository->getStageProgress($playerId)
            ->map(static fn ($progress): array => [
                'chapter_id' => $progress->chapter_id,
                'node_id' => $progress->node_id,
                'difficulty_id' => $progress->difficulty_id,
                'is_first_clear' => (bool) $progress->is_first_clear,
                'clear_count' => (int) $progress->clear_count,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function buildDungeonProgressPayload(int $playerId): array
    {
        return $this->repository->getDungeonProgress($playerId)
            ->map(static fn ($progress): array => [
                'dungeon_id' => $progress->dungeon_id,
                'difficulty_id' => $progress->difficulty_id,
                'is_first_clear' => (bool) $progress->is_first_clear,
                'clear_count' => (int) $progress->clear_count,
                'daily_count' => (int) $progress->daily_count,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $inventory
     * @return array<string, mixed>
     */
    private function buildPlayerPayload(PlayerProfile $playerProfile, array $inventory): array
    {
        $stats = $this->calculateTotalStats($playerProfile);

        return [
            'player_id' => (int) $playerProfile->player_id,
            'nickname' => $playerProfile->nickname,
            'class_id' => (string) $playerProfile->class_id,
            'level' => (int) $playerProfile->level,
            'exp' => (int) $playerProfile->exp,
            'hp' => (int) $stats['max_hp'],
            'max_hp' => (int) $stats['max_hp'],
            'power' => (int) $stats['power'],
            'gold' => (int) $playerProfile->gold,
            'jade' => (int) $playerProfile->jade,
            'contribution' => (int) $playerProfile->contribution,
            'current_chapter_id' => $playerProfile->current_chapter_id,
            'current_node_id' => $playerProfile->current_node_id,
            'max_energy' => (int) $playerProfile->max_energy,
            'class_profile' => $this->getClassCombatProfile((string) $playerProfile->class_id),
            'skill_points' => (int) $playerProfile->skill_points,
            'skill_levels' => $playerProfile->skill_levels ?? [],
            'equipment_summary' => $playerProfile->equipment_summary ?? [],
            'inventory' => $inventory,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStarterProfile(int $playerId, string $nickname): array
    {
        $starter = config('game_runtime.starter_player', []);

        return [
            'player_id' => $playerId,
            'nickname' => $nickname !== '' ? $nickname : "巡厄弟子 {$playerId}",
            'class_id' => $starter['class_id'] ?? null,
            'level' => (int) ($starter['level'] ?? 1),
            'exp' => (int) ($starter['exp'] ?? 0),
            'power' => (int) ($starter['power'] ?? 0),
            'gold' => (int) ($starter['gold'] ?? 0),
            'jade' => (int) ($starter['jade'] ?? 0),
            'contribution' => (int) ($starter['contribution'] ?? 0),
            'current_chapter_id' => $starter['current_chapter_id'] ?? null,
            'current_node_id' => $starter['current_node_id'] ?? null,
            'max_hp' => (int) ($starter['max_hp'] ?? 850),
            'max_energy' => (int) ($starter['max_energy'] ?? 100),
            'skill_points' => (int) ($starter['skill_points'] ?? 0),
            'skill_levels' => $starter['skill_levels'] ?? [],
            'equipment_summary' => $starter['equipment_summary'] ?? [],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildStarterInventoryRows(int $playerId): array
    {
        $starter = config('game_runtime.starter_player.inventory', []);
        $timestamp = Carbon::now();

        return collect($starter)
            ->filter(static fn (array $entry): bool => (string) ($entry['item_id'] ?? '') !== '' && (int) ($entry['count'] ?? 0) > 0)
            ->map(static fn (array $entry): array => [
                'player_id' => $playerId,
                'item_id' => (string) $entry['item_id'],
                'count' => (int) $entry['count'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $skillLevels
     * @return array<string, mixed>
     */
    private function primeSkillLevelsForClass(array $skillLevels, string $classId): array
    {
        if ($classId === '') {
            return $skillLevels;
        }

        Skill::query()
            ->where('class_id', $classId)
            ->orderBy('skill_id')
            ->pluck('skill_id')
            ->each(function (string $skillId) use (&$skillLevels): void {
                if (! array_key_exists($skillId, $skillLevels)) {
                    $skillLevels[$skillId] = 1;
                }
            });

        return $skillLevels;
    }

    private function getResourceName(string $classId): string
    {
        return match ($classId) {
            'class_jingang' => '罡气',
            'class_lingyu' => '灵羽',
            'class_fulu' => '符炁',
            default => '灵力',
        };
    }

    /**
     * @return array<string, int|float>
     */
    private function getClassStatBonuses(string $classId): array
    {
        return match ($classId) {
            'class_jingang' => [
                'bonus_atk' => 4,
                'bonus_def' => 12,
                'bonus_hp' => 160,
                'bonus_damage_ratio' => 0.04,
            ],
            'class_lingyu' => [
                'bonus_atk' => 14,
                'bonus_def' => -3,
                'bonus_hp' => -70,
                'bonus_attack_speed' => 0.12,
                'bonus_damage_ratio' => 0.08,
            ],
            'class_fulu' => [
                'bonus_atk' => 10,
                'bonus_def' => -1,
                'bonus_hp' => 40,
                'bonus_boss_dmg' => 6,
                'bonus_damage_ratio' => 0.12,
            ],
            default => [],
        };
    }

    /**
     * @return array<string, int|float|string>
     */
    private function getClassCombatProfile(string $classId): array
    {
        return match ($classId) {
            'class_jingang' => [
                'role' => 'melee_tank',
                'preferred_range' => 78,
                'move_speed' => 186,
                'attack_range' => 88,
                'attack_interval' => 1.05,
                'resource_regen' => 11,
                'target_priority' => 'nearest',
                'kite_distance' => 0,
            ],
            'class_lingyu' => [
                'role' => 'ranged_dps',
                'preferred_range' => 164,
                'move_speed' => 208,
                'attack_range' => 172,
                'attack_interval' => 0.82,
                'resource_regen' => 14,
                'target_priority' => 'farthest_cluster',
                'kite_distance' => 108,
            ],
            'class_fulu' => [
                'role' => 'caster_control',
                'preferred_range' => 150,
                'move_speed' => 194,
                'attack_range' => 156,
                'attack_interval' => 0.92,
                'resource_regen' => 13,
                'target_priority' => 'boss_or_high_threat',
                'kite_distance' => 84,
            ],
            default => [
                'role' => 'adventurer',
                'preferred_range' => 100,
                'move_speed' => 190,
                'attack_range' => 84,
                'attack_interval' => 1.0,
                'resource_regen' => 12,
                'target_priority' => 'nearest',
                'kite_distance' => 0,
            ],
        };
    }
}
