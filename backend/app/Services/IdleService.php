<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\PlayerProfile;
use App\Repositories\Contracts\IdleRuntimeRepositoryInterface;
use App\Repositories\Contracts\PlayerRuntimeRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IdleService
{
    public function __construct(
        private readonly IdleRuntimeRepositoryInterface $idleRuntimeRepository,
        private readonly PlayerRuntimeRepositoryInterface $playerRuntimeRepository,
        private readonly InventoryService $inventoryService,
        private readonly PlayerRuntimeService $playerRuntimeService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function status(PlayerProfile $playerProfile): array
    {
        return $this->buildIdleStatusPayload(
            $this->playerRuntimeRepository->refreshProfile($playerProfile),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function claim(PlayerProfile $playerProfile): array
    {
        return DB::transaction(function () use ($playerProfile): array {
            $lockedProfile = $this->playerRuntimeRepository->getProfileForUpdate((int) $playerProfile->player_id);

            if (! $lockedProfile) {
                throw new ApiException('玩家不存在', 40442, 404);
            }

            $status = $this->buildIdleStatusPayload($lockedProfile);
            $claimableSeconds = (int) ($status['claimable_seconds'] ?? 0);
            $rewards = $status['rewards'] ?? [];

            if ($claimableSeconds <= 0 || $rewards === []) {
                throw new ApiException('暂无可领取收益', 40095, 400);
            }

            $rewardResult = $this->inventoryService->applyRewards($lockedProfile, $rewards);
            $updatedProfile = $this->playerRuntimeRepository->updateProfile($rewardResult['player_profile'], [
                'idle_started_at' => Carbon::now(),
                'idle_last_claimed_at' => Carbon::now(),
                'last_active_at' => Carbon::now(),
            ]);
            $updatedProfile = $this->playerRuntimeService->syncComputedFields($updatedProfile);

            return [
                'claimed_rewards' => $rewards,
                'status' => $this->buildIdleStatusPayload($updatedProfile),
                'inventory' => $this->playerRuntimeService->buildInventoryPayload((int) $updatedProfile->player_id),
                'player' => $this->playerRuntimeService->getInitPayload($updatedProfile)['player'],
                'currencies' => [
                    'gold' => (int) $updatedProfile->gold,
                    'jade' => (int) $updatedProfile->jade,
                    'contribution' => (int) $updatedProfile->contribution,
                ],
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(PlayerProfile $playerProfile): array
    {
        $matchedRule = $this->idleRuntimeRepository->resolveRuleForLevel((int) $playerProfile->level);

        return [
            'matched_rule' => $matchedRule ? $this->serializeRule($matchedRule) : null,
            'rules' => $this->idleRuntimeRepository->getOpenRules()
                ->map(fn ($rule): array => $this->serializeRule($rule))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildIdleStatusPayload(PlayerProfile $playerProfile): array
    {
        $rule = $this->idleRuntimeRepository->resolveRuleForLevel((int) $playerProfile->level);
        $claimBaseAt = $playerProfile->idle_last_claimed_at ?? $playerProfile->created_at ?? Carbon::now();
        $now = Carbon::now();
        $elapsedSeconds = max($claimBaseAt->diffInSeconds($now), 0);
        $offlineSeconds = $playerProfile->last_active_at
            ? max($playerProfile->last_active_at->diffInSeconds($now), 0)
            : $elapsedSeconds;
        $capSeconds = max((int) (($rule?->idle_cap_hours ?? 0) * 3600), 0);
        $claimableSeconds = min($elapsedSeconds, $capSeconds);
        $rewards = $rule ? $this->calculateRewards($rule->reward_rate ?? [], $claimableSeconds) : [];

        return [
            'accumulated_seconds' => $elapsedSeconds,
            'offline_seconds' => $offlineSeconds,
            'claimable_seconds' => $claimableSeconds,
            'cap_seconds' => $capSeconds,
            'is_capped' => $capSeconds > 0 && $elapsedSeconds >= $capSeconds,
            'source_hint' => $this->resolveSourceHint($playerProfile),
            'rule' => $rule ? $this->serializeRule($rule) : null,
            'rewards' => $rewards,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rewardRate
     * @return list<array<string, mixed>>
     */
    private function calculateRewards(array $rewardRate, int $seconds): array
    {
        $rewards = [];

        foreach ($rewardRate as $entry) {
            $itemId = (string) ($entry['item_id'] ?? '');
            $countPerHour = max((float) ($entry['count_per_hour'] ?? 0), 0.0);
            $count = (int) floor($countPerHour * ($seconds / 3600));

            if ($itemId === '' || $count <= 0) {
                continue;
            }

            $rewards[] = [
                'item_id' => $itemId,
                'count' => $count,
            ];
        }

        return $rewards;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeRule($rule): array
    {
        return [
            'rule_id' => $rule->rule_id,
            'rule_name' => $rule->rule_name,
            'min_level' => (int) $rule->min_level,
            'max_level' => (int) $rule->max_level,
            'idle_cap_hours' => (int) $rule->idle_cap_hours,
            'reward_rate' => $rule->reward_rate ?? [],
            'bonus_hint' => (string) ($rule->bonus_hint ?? ''),
        ];
    }

    private function resolveSourceHint(PlayerProfile $playerProfile): string
    {
        $nodeId = (string) ($playerProfile->current_node_id ?? '');

        if ($nodeId !== '') {
            return "当前挂机收益按主线推进 {$nodeId} 所在阶段结算。";
        }

        return '挂机收益与在线刷图共享同一成长材料循环。';
    }
}
