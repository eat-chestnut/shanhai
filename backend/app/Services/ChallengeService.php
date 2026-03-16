<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\BattleRecord;
use App\Models\ChallengeConfig;
use App\Models\PlayerChallengeProgress;
use App\Models\PlayerProfile;
use App\Repositories\Contracts\ChallengeRuntimeRepositoryInterface;
use Illuminate\Support\Carbon;

class ChallengeService
{
    public function __construct(
        private readonly ChallengeRuntimeRepositoryInterface $challengeRuntimeRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function list(PlayerProfile $playerProfile): array
    {
        $items = $this->challengeRuntimeRepository->getOpenChallenges()
            ->map(fn (ChallengeConfig $challenge): array => $this->buildChallengeListItem($playerProfile, $challenge))
            ->values()
            ->all();

        return [
            'challenges' => $items,
            'week_key' => $this->currentWeekKey(),
            'next_reset_at' => $this->nextResetAt()->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(PlayerProfile $playerProfile, string $challengeId): array
    {
        $challenge = $this->requireChallenge($challengeId);
        $progress = $this->getFreshProgress((int) $playerProfile->player_id, $challengeId);
        $isUnlocked = (int) $playerProfile->level >= (int) $challenge->unlock_level;

        return [
            'challenge' => [
                'challenge_id' => $challenge->challenge_id,
                'challenge_name' => $challenge->challenge_name,
                'challenge_type' => $challenge->challenge_type,
                'challenge_desc' => $challenge->challenge_desc,
                'unlock_level' => (int) $challenge->unlock_level,
                'is_unlocked' => $isUnlocked,
                'unlock_text' => $isUnlocked ? '可挑战' : sprintf('需达到 Lv.%d', (int) $challenge->unlock_level),
                'highest_floor' => (int) ($progress?->highest_floor ?? 0),
                'weekly_highest_floor' => (int) ($progress?->weekly_highest_floor ?? 0),
                'current_floor' => (int) ($progress?->current_floor ?? 1),
                'reward_preview' => $challenge->reward_preview ?? [],
                'week_key' => $this->currentWeekKey(),
                'next_reset_at' => $this->nextResetAt()->toIso8601String(),
                'floors' => $this->buildFloorPayloads($playerProfile, $challenge, $progress),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function assertAccess(PlayerProfile $playerProfile, string $challengeId, string $floorId): array
    {
        $challenge = $this->requireChallenge($challengeId);
        $progress = $this->getFreshProgress((int) $playerProfile->player_id, $challengeId);

        if (! $challenge->is_open) {
            throw new ApiException('挑战未开放', 40096, 400);
        }

        if ((int) $playerProfile->level < (int) $challenge->unlock_level) {
            throw new ApiException('挑战未解锁', 40097, 400);
        }

        $floor = $this->findFloor($challenge, $floorId);
        $highestFloor = (int) ($progress?->highest_floor ?? 0);
        $unlockedFloor = $highestFloor + 1;

        if ((int) $floor['floor'] > $unlockedFloor) {
            throw new ApiException('挑战层数未解锁', 40098, 400);
        }

        return [
            'challenge' => $challenge,
            'floor' => $floor,
            'progress' => $progress,
        ];
    }

    /**
     * @return array{monster_group_id:string,monster_ids:list<string>}
     */
    public function resolveEncounterDefinition(string $challengeId, string $floorId): array
    {
        $challenge = $this->requireChallenge($challengeId);
        $floor = $this->findFloor($challenge, $floorId);

        return [
            'monster_group_id' => (string) ($floor['monster_group_id'] ?? sprintf('%s_%s', $challengeId, $floorId)),
            'monster_ids' => array_values(array_filter(
                $floor['monster_ids'] ?? [],
                static fn ($monsterId): bool => (string) $monsterId !== '',
            )),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function resolveNormalRewards(string $challengeId, string $floorId): array
    {
        $challenge = $this->requireChallenge($challengeId);
        $floor = $this->findFloor($challenge, $floorId);

        return $this->resolveRewardGroupItems((string) ($floor['normal_reward_group_id'] ?? ''));
    }

    /**
     * @return array<string, mixed>
     */
    public function settleChallenge(PlayerProfile $playerProfile, BattleRecord $battleRecord, bool $isVictory): array
    {
        $challengeId = (string) ($battleRecord->request_snapshot['challenge_id'] ?? '');
        $floorId = (string) ($battleRecord->difficulty_id ?? '');
        $challenge = $this->requireChallenge($challengeId);
        $floor = $this->findFloor($challenge, $floorId);
        $progress = $this->getFreshProgress((int) $playerProfile->player_id, $challengeId, true);
        $firstClearRewards = [];
        $weeklyRewards = [];

        if (! $isVictory) {
            return [
                'source_type' => 'challenge',
                'challenge_id' => $challengeId,
                'floor_id' => $floorId,
                'highest_floor' => (int) ($progress?->highest_floor ?? 0),
                'weekly_highest_floor' => (int) ($progress?->weekly_highest_floor ?? 0),
                'first_clear_rewards' => [],
                'weekly_rewards' => [],
            ];
        }

        $firstClearFloors = array_values($progress?->first_clear_floors ?? []);
        $weeklyClaimedFloors = array_values($progress?->weekly_reward_claimed_floors ?? []);
        $floorNumber = (int) ($floor['floor'] ?? 1);
        $isFirstClearNow = ! in_array($floorId, $firstClearFloors, true);
        $isWeeklyRewardNow = ! in_array($floorId, $weeklyClaimedFloors, true);

        if ($isFirstClearNow) {
            $firstClearFloors[] = $floorId;
            $firstClearRewards = $this->resolveRewardGroupItems((string) ($floor['first_clear_reward_group_id'] ?? ''));
        }

        if ($isWeeklyRewardNow) {
            $weeklyClaimedFloors[] = $floorId;
            $weeklyRewards = $this->resolveRewardGroupItems((string) ($floor['weekly_reward_group_id'] ?? ''));
        }

        $updatedProgress = $this->challengeRuntimeRepository->upsertPlayerProgress(
            [
                'player_id' => (int) $playerProfile->player_id,
                'challenge_id' => $challengeId,
            ],
            [
                'week_key' => $this->currentWeekKey(),
                'highest_floor' => max((int) ($progress?->highest_floor ?? 0), $floorNumber),
                'current_floor' => min($floorNumber + 1, count($challenge->floors ?? [])),
                'weekly_highest_floor' => max((int) ($progress?->weekly_highest_floor ?? 0), $floorNumber),
                'clear_count' => (int) ($progress?->clear_count ?? 0) + 1,
                'weekly_clear_count' => (int) ($progress?->weekly_clear_count ?? 0) + 1,
                'first_clear_floors' => array_values(array_unique($firstClearFloors)),
                'weekly_reward_claimed_floors' => array_values(array_unique($weeklyClaimedFloors)),
                'last_cleared_floor' => $floorNumber,
                'last_cleared_at' => Carbon::now(),
            ],
        );

        return [
            'source_type' => 'challenge',
            'challenge_id' => $challengeId,
            'challenge_name' => $challenge->challenge_name,
            'floor_id' => $floorId,
            'floor' => $floorNumber,
            'highest_floor' => (int) $updatedProgress->highest_floor,
            'weekly_highest_floor' => (int) $updatedProgress->weekly_highest_floor,
            'is_first_clear_now' => $isFirstClearNow,
            'first_clear_rewards' => $firstClearRewards,
            'weekly_rewards' => $weeklyRewards,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildChallengeListItem(PlayerProfile $playerProfile, ChallengeConfig $challenge): array
    {
        $progress = $this->getFreshProgress((int) $playerProfile->player_id, $challenge->challenge_id);
        $isUnlocked = (int) $playerProfile->level >= (int) $challenge->unlock_level;
        $nextFloor = $this->findFloorByNumber($challenge, max((int) ($progress?->highest_floor ?? 0) + 1, 1));

        return [
            'challenge_id' => $challenge->challenge_id,
            'challenge_name' => $challenge->challenge_name,
            'challenge_type' => $challenge->challenge_type,
            'challenge_desc' => $challenge->challenge_desc,
            'unlock_level' => (int) $challenge->unlock_level,
            'is_unlocked' => $isUnlocked,
            'unlock_text' => $isUnlocked ? '可挑战' : sprintf('需达到 Lv.%d', (int) $challenge->unlock_level),
            'highest_floor' => (int) ($progress?->highest_floor ?? 0),
            'weekly_highest_floor' => (int) ($progress?->weekly_highest_floor ?? 0),
            'current_floor' => (int) ($progress?->current_floor ?? 1),
            'recommended_power' => (int) ($nextFloor['recommended_power'] ?? 0),
            'reward_preview' => $nextFloor !== [] ? $this->buildRewardPreview($nextFloor) : ($challenge->reward_preview ?? []),
            'next_floor_id' => (string) ($nextFloor['floor_id'] ?? ''),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildFloorPayloads(PlayerProfile $playerProfile, ChallengeConfig $challenge, ?PlayerChallengeProgress $progress): array
    {
        $highestFloor = (int) ($progress?->highest_floor ?? 0);
        $firstClearFloors = array_values($progress?->first_clear_floors ?? []);
        $weeklyClaimedFloors = array_values($progress?->weekly_reward_claimed_floors ?? []);

        return collect($challenge->floors ?? [])
            ->map(function (array $floor) use ($firstClearFloors, $highestFloor, $playerProfile, $weeklyClaimedFloors): array {
                $floorNumber = (int) ($floor['floor'] ?? 1);
                $isUnlocked = (int) $playerProfile->level >= (int) ($floor['unlock_level'] ?? 1) && $floorNumber <= $highestFloor + 1;
                $isCleared = in_array((string) ($floor['floor_id'] ?? ''), $firstClearFloors, true);
                $weeklyClaimed = in_array((string) ($floor['floor_id'] ?? ''), $weeklyClaimedFloors, true);
                $recommendedPower = (int) ($floor['recommended_power'] ?? 0);

                return [
                    'floor_id' => (string) ($floor['floor_id'] ?? ''),
                    'floor' => $floorNumber,
                    'floor_name' => (string) ($floor['floor_name'] ?? sprintf('第 %d 层', $floorNumber)),
                    'recommended_power' => $recommendedPower,
                    'monster_group_id' => (string) ($floor['monster_group_id'] ?? ''),
                    'monster_ids' => array_values(array_filter(
                        $floor['monster_ids'] ?? [],
                        static fn ($monsterId): bool => (string) $monsterId !== '',
                    )),
                    'is_unlocked' => $isUnlocked,
                    'is_cleared' => $isCleared,
                    'is_first_clear_claimed' => $isCleared,
                    'is_weekly_reward_claimed' => $weeklyClaimed,
                    'reward_preview' => $this->buildRewardPreview($floor),
                    'is_recommended' => (int) $playerProfile->power >= max((int) floor($recommendedPower * 0.9), 1),
                ];
            })
            ->values()
            ->all();
    }

    private function requireChallenge(string $challengeId): ChallengeConfig
    {
        $challenge = $this->challengeRuntimeRepository->findChallenge($challengeId);

        if (! $challenge) {
            throw new ApiException('挑战不存在', 40496, 404);
        }

        return $challenge;
    }

    /**
     * @return array<string, mixed>
     */
    private function findFloor(ChallengeConfig $challenge, string $floorId): array
    {
        foreach ($challenge->floors ?? [] as $floor) {
            if ((string) ($floor['floor_id'] ?? '') === $floorId) {
                return $floor;
            }
        }

        throw new ApiException('挑战层不存在', 40497, 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function findFloorByNumber(ChallengeConfig $challenge, int $floorNumber): array
    {
        foreach ($challenge->floors ?? [] as $floor) {
            if ((int) ($floor['floor'] ?? 0) === $floorNumber) {
                return $floor;
            }
        }

        return [];
    }

    private function getFreshProgress(int $playerId, string $challengeId, bool $forUpdate = false): ?PlayerChallengeProgress
    {
        $progress = $this->challengeRuntimeRepository->findPlayerProgress($playerId, $challengeId, $forUpdate);

        if (! $progress) {
            return null;
        }

        if ((string) $progress->week_key === $this->currentWeekKey()) {
            return $progress;
        }

        return $this->challengeRuntimeRepository->upsertPlayerProgress(
            [
                'player_id' => $playerId,
                'challenge_id' => $challengeId,
            ],
            [
                'week_key' => $this->currentWeekKey(),
                'highest_floor' => (int) $progress->highest_floor,
                'current_floor' => max((int) $progress->highest_floor + 1, 1),
                'weekly_highest_floor' => 0,
                'clear_count' => (int) $progress->clear_count,
                'weekly_clear_count' => 0,
                'first_clear_floors' => $progress->first_clear_floors ?? [],
                'weekly_reward_claimed_floors' => [],
                'last_cleared_floor' => (int) $progress->last_cleared_floor,
                'last_cleared_at' => $progress->last_cleared_at,
            ],
        );
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

        return array_values(array_filter(array_map(
            static fn (array $entry): array => [
                'item_id' => (string) ($entry['item_id'] ?? ''),
                'count' => max((int) ($entry['count'] ?? 0), 0),
            ],
            $items,
        ), static fn (array $entry): bool => $entry['item_id'] !== '' && $entry['count'] > 0));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildRewardPreview(array $floor): array
    {
        return array_merge(
            $this->resolveRewardGroupItems((string) ($floor['normal_reward_group_id'] ?? '')),
            $this->resolveRewardGroupItems((string) ($floor['weekly_reward_group_id'] ?? '')),
        );
    }

    private function currentWeekKey(): string
    {
        return Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    private function nextResetAt(): Carbon
    {
        return Carbon::now()->startOfWeek(Carbon::MONDAY)->addWeek();
    }
}
