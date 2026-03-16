<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\BattleRecord;
use App\Models\PlayerProfile;
use App\Models\PlayerStageProgress;
use App\Models\TaskConfig;
use App\Repositories\Contracts\PlayerRuntimeRepositoryInterface;
use App\Repositories\Contracts\TaskRuntimeRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function __construct(
        private readonly TaskRuntimeRepositoryInterface $taskRuntimeRepository,
        private readonly PlayerRuntimeRepositoryInterface $playerRuntimeRepository,
        private readonly InventoryService $inventoryService,
        private readonly PlayerRuntimeService $playerRuntimeService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function list(PlayerProfile $playerProfile): array
    {
        return $this->buildTaskListPayload($playerProfile);
    }

    /**
     * @return array<string, mixed>
     */
    public function claim(PlayerProfile $playerProfile, string $taskId): array
    {
        return DB::transaction(function () use ($playerProfile, $taskId): array {
            $lockedProfile = $this->playerRuntimeRepository->getProfileForUpdate((int) $playerProfile->player_id);

            if (! $lockedProfile) {
                throw new ApiException('玩家不存在', 40442, 404);
            }

            $task = $this->taskRuntimeRepository->findTaskConfig($taskId);

            if (! $task) {
                throw new ApiException('任务不存在', 40471, 404);
            }

            $runtimeTask = $this->buildRuntimeTask($lockedProfile, $task);

            if (! ($runtimeTask['can_claim'] ?? false)) {
                throw new ApiException('任务不可领取', 40071, 400);
            }

            $cycleKey = (string) $runtimeTask['cycle_key'];
            $this->taskRuntimeRepository->upsertPlayerProgress(
                [
                    'player_id' => (int) $lockedProfile->player_id,
                    'task_id' => $task->task_id,
                    'cycle_key' => $cycleKey,
                ],
                [
                    'progress' => (int) $runtimeTask['progress'],
                    'is_claimed' => true,
                    'claimed_at' => Carbon::now(),
                ],
            );

            $rewardResult = $this->inventoryService->applyRewards($lockedProfile, $task->rewards ?? []);
            $updatedProfile = $this->playerRuntimeService->syncComputedFields($rewardResult['player_profile']);

            return [
                'claimed_task_ids' => [$task->task_id],
                'rewards' => $task->rewards ?? [],
                'tasks' => $this->buildTaskListPayload($updatedProfile)['tasks'],
                'inventory' => $this->playerRuntimeService->buildInventoryPayload((int) $updatedProfile->player_id),
                'player' => $this->playerRuntimeService->getInitPayload($updatedProfile)['player'],
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function claimAll(PlayerProfile $playerProfile): array
    {
        return DB::transaction(function () use ($playerProfile): array {
            $lockedProfile = $this->playerRuntimeRepository->getProfileForUpdate((int) $playerProfile->player_id);

            if (! $lockedProfile) {
                throw new ApiException('玩家不存在', 40442, 404);
            }

            $taskPayload = $this->buildTaskListPayload($lockedProfile);
            $claimableTasks = array_values(array_filter(
                $taskPayload['tasks'],
                static fn (array $task): bool => (bool) ($task['can_claim'] ?? false),
            ));

            if ($claimableTasks === []) {
                throw new ApiException('任务不可领取', 40071, 400);
            }

            $claimedTaskIds = [];
            $mergedRewards = [];

            foreach ($claimableTasks as $task) {
                $claimedTaskIds[] = (string) $task['task_id'];
                $mergedRewards = array_merge($mergedRewards, $task['rewards'] ?? []);

                $this->taskRuntimeRepository->upsertPlayerProgress(
                    [
                        'player_id' => (int) $lockedProfile->player_id,
                        'task_id' => (string) $task['task_id'],
                        'cycle_key' => (string) $task['cycle_key'],
                    ],
                    [
                        'progress' => (int) $task['progress'],
                        'is_claimed' => true,
                        'claimed_at' => Carbon::now(),
                    ],
                );
            }

            $rewardResult = $this->inventoryService->applyRewards($lockedProfile, $this->mergeRewards($mergedRewards));
            $updatedProfile = $this->playerRuntimeService->syncComputedFields($rewardResult['player_profile']);

            return [
                'claimed_task_ids' => $claimedTaskIds,
                'rewards' => $this->mergeRewards($mergedRewards),
                'tasks' => $this->buildTaskListPayload($updatedProfile)['tasks'],
                'inventory' => $this->playerRuntimeService->buildInventoryPayload((int) $updatedProfile->player_id),
                'player' => $this->playerRuntimeService->getInitPayload($updatedProfile)['player'],
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTaskListPayload(PlayerProfile $playerProfile): array
    {
        $tasks = $this->taskRuntimeRepository->getOpenTaskConfigs()
            ->map(fn (TaskConfig $taskConfig): array => $this->buildRuntimeTask($playerProfile, $taskConfig))
            ->values()
            ->all();

        return [
            'tasks' => $tasks,
            'has_claimable' => collect($tasks)->contains(static fn (array $task): bool => (bool) ($task['can_claim'] ?? false)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRuntimeTask(PlayerProfile $playerProfile, TaskConfig $taskConfig): array
    {
        $cycleKey = $taskConfig->task_type === 'daily'
            ? Carbon::now()->toDateString()
            : 'permanent';
        $progress = min($this->calculateProgress($playerProfile, $taskConfig), (int) $taskConfig->target);
        $progressRecord = $this->taskRuntimeRepository->findPlayerProgress(
            (int) $playerProfile->player_id,
            $taskConfig->task_id,
            $cycleKey,
        );

        return [
            'task_id' => $taskConfig->task_id,
            'task_type' => $taskConfig->task_type,
            'task_name' => $taskConfig->task_name,
            'task_desc' => $taskConfig->task_desc,
            'progress' => $progress,
            'target' => (int) $taskConfig->target,
            'rewards' => $taskConfig->rewards ?? [],
            'can_claim' => $progress >= (int) $taskConfig->target && ! (bool) ($progressRecord?->is_claimed ?? false),
            'is_claimed' => (bool) ($progressRecord?->is_claimed ?? false),
            'cycle_key' => $cycleKey,
        ];
    }

    private function calculateProgress(PlayerProfile $playerProfile, TaskConfig $taskConfig): int
    {
        $conditions = $taskConfig->conditions ?? [];

        return match ($taskConfig->target_type) {
            'level_reach' => (int) $playerProfile->level,
            'stage_clear' => $this->countStageClearProgress((int) $playerProfile->player_id, $conditions),
            'dungeon_clear' => $this->countTodayBattles((int) $playerProfile->player_id, 'dungeon'),
            'battle_complete' => $this->countTodayBattles((int) $playerProfile->player_id),
            default => 0,
        };
    }

    /**
     * @param  array<string, mixed>  $conditions
     */
    private function countStageClearProgress(int $playerId, array $conditions): int
    {
        $query = PlayerStageProgress::query()
            ->where('player_id', $playerId);

        if ((string) ($conditions['node_id'] ?? '') !== '') {
            $query->where('node_id', (string) $conditions['node_id']);
        }

        if ((string) ($conditions['difficulty_id'] ?? '') !== '') {
            $query->where('difficulty_id', (string) $conditions['difficulty_id']);
        }

        return (int) $query->sum('clear_count');
    }

    private function countTodayBattles(int $playerId, string $sourceType = ''): int
    {
        $query = BattleRecord::query()
            ->where('player_id', $playerId)
            ->where('status', 'settled')
            ->whereDate('settled_at', Carbon::now()->toDateString());

        if ($sourceType !== '') {
            $query->where('source_type', $sourceType);
        }

        return (int) $query->count();
    }

    /**
     * @param  list<array<string, mixed>>  $rewards
     * @return list<array<string, mixed>>
     */
    private function mergeRewards(array $rewards): array
    {
        $merged = [];

        foreach ($rewards as $reward) {
            $itemId = (string) ($reward['item_id'] ?? '');
            $count = max((int) ($reward['count'] ?? 0), 0);

            if ($itemId === '' || $count <= 0) {
                continue;
            }

            $merged[$itemId] = ($merged[$itemId] ?? 0) + $count;
        }

        return collect($merged)
            ->map(static fn (int $count, string $itemId): array => [
                'item_id' => $itemId,
                'count' => $count,
            ])
            ->values()
            ->all();
    }
}
