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
            ->orderBy('chapter_id')
            ->get()
            ->map(function (MainlineChapter $chapter) use ($playerProfile, $progress): array {
                $chapterUnlocked = (int) $playerProfile->level >= (int) $chapter->unlock_level;
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
                    ];
                }

                return [
                    'chapter_id' => $chapter->chapter_id,
                    'chapter_name' => $chapter->chapter_name,
                    'unlock_level' => (int) $chapter->unlock_level,
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

        if ((int) $playerProfile->level < $levelRequirement) {
            return false;
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
}
