<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\MainlineChapter;
use App\Models\MainlineDifficulty;
use App\Models\MainlineNode;
use App\Models\PlayerProfile;
use App\Repositories\Contracts\PlayerRuntimeRepositoryInterface;
use Illuminate\Support\Collection;

class StageRuntimeService
{
    public function __construct(
        private readonly PlayerRuntimeRepositoryInterface $playerRuntimeRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function listChapters(PlayerProfile $playerProfile): array
    {
        $progress = $this->playerRuntimeRepository->getStageProgress($playerProfile->player_id);
        $chapters = MainlineChapter::query()
            ->with([
                'nodes' => fn ($query) => $query->orderBy('node_id'),
            ])
            ->orderBy('sort_order')
            ->get()
            ->map(function (MainlineChapter $chapter) use ($playerProfile, $progress): array {
                $chapterUnlocked = $this->isChapterUnlocked($playerProfile, $chapter, $progress);
                $chapterNodes = [];
                $orderedNodes = $chapter->nodes->sortBy('node_id')->values();

                foreach ($orderedNodes as $index => $node) {
                    $nodeUnlocked = $this->isNodeUnlocked($playerProfile, $orderedNodes, $progress, $node, $index, $chapterUnlocked);
                    $nodeProgressSummary = $progress
                        ->where('node_id', $node->node_id)
                        ->sortByDesc('clear_count')
                        ->first();

                    $chapterNodes[] = [
                        'node_id' => $node->node_id,
                        'chapter_id' => $chapter->chapter_id,
                        'node_name' => $node->node_name,
                        'unlock_condition' => $node->unlock_condition ?? ['level' => 1],
                        'difficulty_ids' => $node->difficulty_ids ?? [],
                        'is_unlocked' => $nodeUnlocked,
                        'clear_count' => (int) ($nodeProgressSummary?->clear_count ?? 0),
                        'is_current' => $playerProfile->current_node_id === $node->node_id,
                        'progress_state' => $this->resolveNodeProgressState(
                            $playerProfile,
                            $node->node_id,
                            $nodeUnlocked,
                            (int) ($nodeProgressSummary?->clear_count ?? 0),
                        ),
                    ];
                }

                return [
                    'chapter_id' => $chapter->chapter_id,
                    'chapter_name' => $chapter->chapter_name,
                    'unlock_level' => (int) $chapter->unlock_level,
                    'sort_order' => (int) ($chapter->sort_order ?? 0),
                    'required_previous_chapter' => $chapter->required_previous_chapter,
                    'required_previous_highest_difficulty' => $chapter->required_previous_highest_difficulty,
                    'is_unlocked' => $chapterUnlocked,
                    'is_current' => $playerProfile->current_chapter_id === $chapter->chapter_id,
                    'nodes' => $chapterNodes,
                ];
            })
            ->values()
            ->all();

        return [
            'chapters' => $chapters,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getNodeDetail(PlayerProfile $playerProfile, string $nodeId): array
    {
        [$chapter, $node, $difficulties, $difficultyStates] = $this->resolveNodeContext($playerProfile, $nodeId);

        return [
            'chapter_id' => $chapter->chapter_id,
            'chapter_name' => $chapter->chapter_name,
            'node' => [
                'node_id' => $node->node_id,
                'chapter_id' => $node->chapter_id,
                'node_name' => $node->node_name,
                'unlock_condition' => $node->unlock_condition ?? ['level' => 1],
                'difficulty_ids' => $node->difficulty_ids ?? [],
                'is_unlocked' => (bool) $difficultyStates['node_unlocked'],
                'progress_state' => $this->resolveNodeProgressState(
                    $playerProfile,
                    $node->node_id,
                    (bool) $difficultyStates['node_unlocked'],
                    (int) collect($difficulties)->max('clear_count'),
                ),
                'difficulties' => $difficulties,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function listDifficulties(PlayerProfile $playerProfile, string $nodeId): array
    {
        [, $node, $difficulties] = $this->resolveNodeContext($playerProfile, $nodeId);

        return [
            'node_id' => $node->node_id,
            'difficulties' => $difficulties,
        ];
    }

    /**
     * @return array{chapter:MainlineChapter,node:MainlineNode,difficulty:MainlineDifficulty}
     */
    public function assertStageAccess(PlayerProfile $playerProfile, string $nodeId, string $difficultyId): array
    {
        [$chapter, $node, $difficulties, $difficultyStates] = $this->resolveNodeContext($playerProfile, $nodeId);

        if (! $difficultyStates['node_unlocked']) {
            throw new ApiException('节点未解锁', 40041, 400);
        }

        $difficulty = $difficultyStates['difficulty_map']->get($difficultyId);

        if (! $difficulty instanceof MainlineDifficulty) {
            throw new ApiException('难度不存在', 40412, 404);
        }

        $difficultyPayload = collect($difficulties)->firstWhere('difficulty_id', $difficultyId);

        if (! ($difficultyPayload['is_unlocked'] ?? false)) {
            throw new ApiException('难度未解锁', 40042, 400);
        }

        return [
            'chapter' => $chapter,
            'node' => $node,
            'difficulty' => $difficulty,
        ];
    }

    /**
     * @return array{0:MainlineChapter,1:MainlineNode,2:list<array<string, mixed>>,3:array<string, mixed>}
     */
    private function resolveNodeContext(PlayerProfile $playerProfile, string $nodeId): array
    {
        $node = MainlineNode::query()
            ->with('chapter')
            ->where('node_id', $nodeId)
            ->first();

        if (! $node || ! $node->chapter) {
            throw new ApiException('节点不存在', 40401, 404);
        }

        $chapter = $node->chapter;
        $progress = $this->playerRuntimeRepository->getStageProgress($playerProfile->player_id);
        $orderedNodes = MainlineNode::query()
            ->where('chapter_id', $chapter->chapter_id)
            ->orderBy('node_id')
            ->get()
            ->values();
        $nodeIndex = $orderedNodes->search(static fn (MainlineNode $entry): bool => $entry->node_id === $nodeId);
        $chapterUnlocked = (int) $playerProfile->level >= (int) $chapter->unlock_level;
        $nodeUnlocked = $this->isNodeUnlocked(
            $playerProfile,
            $orderedNodes,
            $progress,
            $node,
            is_int($nodeIndex) ? $nodeIndex : 0,
            $chapterUnlocked,
        );

        $difficultyMap = MainlineDifficulty::query()
            ->where('node_id', $nodeId)
            ->get()
            ->mapWithKeys(static fn (MainlineDifficulty $difficulty): array => [
                $difficulty->difficulty_id => $difficulty,
            ]);
        $orderedDifficultyIds = collect($node->difficulty_ids ?? [])
            ->filter()
            ->values();

        if ($orderedDifficultyIds->isEmpty()) {
            $orderedDifficultyIds = $difficultyMap->keys()->sort()->values();
        }

        $difficulties = [];

        foreach ($orderedDifficultyIds as $index => $difficultyId) {
            $difficulty = $difficultyMap->get($difficultyId);

            if (! $difficulty instanceof MainlineDifficulty) {
                continue;
            }

            $progressRecord = $this->playerRuntimeRepository->findStageProgress(
                $playerProfile->player_id,
                $nodeId,
                $difficulty->difficulty_id,
            );

            $previousDifficultyId = $index > 0 ? (string) $orderedDifficultyIds[$index - 1] : null;
            $previousCleared = true;

            if ($previousDifficultyId !== null) {
                $previousProgress = $this->playerRuntimeRepository->findStageProgress(
                    $playerProfile->player_id,
                    $nodeId,
                    $previousDifficultyId,
                );
                $previousCleared = (int) ($previousProgress?->clear_count ?? 0) > 0;
            }

            $difficulties[] = [
                'difficulty_id' => $difficulty->difficulty_id,
                'node_id' => $difficulty->node_id,
                'recommended_power' => (int) $difficulty->recommended_power,
                'first_clear_reward_group_id' => $difficulty->first_clear_reward_group_id,
                'is_unlocked' => $nodeUnlocked && $previousCleared,
                'is_first_clear' => (bool) ($progressRecord?->is_first_clear ?? false),
                'clear_count' => (int) ($progressRecord?->clear_count ?? 0),
                'progress_state' => $this->resolveDifficultyProgressState(
                    $nodeUnlocked && $previousCleared,
                    (int) ($progressRecord?->clear_count ?? 0),
                    $playerProfile->current_node_id === $nodeId && $difficulty->difficulty_id === ($node->difficulty_ids[0] ?? ''),
                ),
            ];
        }

        return [
            $chapter,
            $node,
            $difficulties,
            [
                'node_unlocked' => $nodeUnlocked,
                'difficulty_map' => $difficultyMap,
            ],
        ];
    }

    private function isNodeUnlocked(
        PlayerProfile $playerProfile,
        Collection $orderedNodes,
        Collection $progress,
        MainlineNode $node,
        int $nodeIndex,
        bool $chapterUnlocked,
    ): bool {
        if (! $chapterUnlocked) {
            return false;
        }

        $levelRequirement = (int) (($node->unlock_condition ?? [])['level'] ?? 1);
        $clearNodeId = (string) (($node->unlock_condition ?? [])['clear_node_id'] ?? (($node->unlock_condition ?? [])['conditions']['clear_node_id'] ?? ''));

        if ((int) $playerProfile->level < $levelRequirement) {
            return false;
        }

        if ($clearNodeId !== '') {
            $requiredClear = $progress
                ->where('node_id', $clearNodeId)
                ->first(fn ($entry): bool => (int) $entry->clear_count > 0);

            if ($requiredClear === null) {
                return false;
            }
        }

        if ($nodeIndex <= 0) {
            return true;
        }

        $previousNode = $orderedNodes[$nodeIndex - 1] ?? null;

        if (! $previousNode instanceof MainlineNode) {
            return true;
        }

        $previousCleared = $progress
            ->where('node_id', $previousNode->node_id)
            ->first(fn ($entry): bool => (int) $entry->clear_count > 0);

        return $previousCleared !== null || $playerProfile->current_node_id === $node->node_id;
    }

    private function isChapterUnlocked(PlayerProfile $playerProfile, MainlineChapter $chapter, Collection $progress): bool
    {
        // 检查等级要求
        if ((int) $playerProfile->level < (int) $chapter->unlock_level) {
            return false;
        }

        // 如果没有前置章节要求，直接解锁
        if (empty($chapter->required_previous_chapter)) {
            return true;
        }

        // 检查前置章节是否存在
        $previousChapter = MainlineChapter::where('chapter_id', $chapter->required_previous_chapter)->first();
        if (! $previousChapter) {
            return false;
        }

        // 获取前置章节的所有节点进度
        $previousChapterNodes = MainlineNode::where('chapter_id', $chapter->required_previous_chapter)->get();
        $allNodeDifficulties = [];

        foreach ($previousChapterNodes as $node) {
            $difficulties = MainlineDifficulty::where('node_id', $node->node_id)->get();
            foreach ($difficulties as $difficulty) {
                $allNodeDifficulties[] = [
                    'node_id' => $node->node_id,
                    'difficulty_id' => $difficulty->difficulty_id,
                    'difficulty_order' => $this->getDifficultyOrder($difficulty->difficulty_id),
                ];
            }
        }

        // 按难度排序
        usort($allNodeDifficulties, fn ($a, $b) => $a['difficulty_order'] <=> $b['difficulty_order']);

        // 找到最高难度
        $highestDifficulty = null;
        $highestOrder = -1;
        foreach ($allNodeDifficulties as $diff) {
            if ($diff['difficulty_order'] > $highestOrder) {
                $highestOrder = $diff['difficulty_order'];
                $highestDifficulty = $diff;
            }
        }

        // 如果没有配置难度要求，只需要检查前置章节有任意通关即可
        if (empty($chapter->required_previous_highest_difficulty)) {
            return $progress->contains(function ($progressRecord) use ($chapter) {
                return $progressRecord->node_id && 
                       MainlineNode::where('node_id', $progressRecord->node_id)
                                  ->where('chapter_id', $chapter->required_previous_chapter)
                                  ->exists() &&
                       (int) $progressRecord->clear_count > 0;
            });
        }

        // 检查是否通关了指定难度的任意节点
        $requiredDifficultyOrder = $this->getDifficultyOrder($chapter->required_previous_highest_difficulty);
        
        return $progress->contains(function ($progressRecord) use ($chapter, $requiredDifficultyOrder) {
            if ((int) $progressRecord->clear_count <= 0) {
                return false;
            }

            $node = MainlineNode::where('node_id', $progressRecord->node_id)
                               ->where('chapter_id', $chapter->required_previous_chapter)
                               ->first();
            
            if (! $node) {
                return false;
            }

            $difficultyOrder = $this->getDifficultyOrder($progressRecord->difficulty_id);
            return $difficultyOrder >= $requiredDifficultyOrder;
        });
    }

    private function getDifficultyOrder(string $difficultyId): int
    {
        return match ($difficultyId) {
            'easy' => 1,
            'normal' => 2,
            'hard' => 3,
            'nightmare' => 4,
            'epic' => 5,
            default => 0,
        };
    }

    private function resolveNodeProgressState(PlayerProfile $playerProfile, string $nodeId, bool $isUnlocked, int $clearCount): string
    {
        if (! $isUnlocked) {
            return 'locked';
        }

        if ($clearCount > 0) {
            return 'cleared';
        }

        if ($playerProfile->current_node_id === $nodeId) {
            return 'current';
        }

        return 'available';
    }

    private function resolveDifficultyProgressState(bool $isUnlocked, int $clearCount, bool $isCurrent): string
    {
        if (! $isUnlocked) {
            return 'locked';
        }

        if ($clearCount > 0) {
            return 'cleared';
        }

        if ($isCurrent) {
            return 'current';
        }

        return 'available';
    }
}
