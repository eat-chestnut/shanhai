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
            ->orderBy('chapter_id')
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

                    $currentDifficultyId = $this->resolveCurrentDifficultyId(
                        $playerProfile,
                        $node->node_id,
                        $node->difficulty_ids ?? [],
                    );

                    $chapterNodes[] = [
                        'node_id' => $node->node_id,
                        'chapter_id' => $chapter->chapter_id,
                        'node_name' => $node->node_name,
                        'unlock_condition' => $node->unlock_condition ?? ['level' => 1],
                        'difficulty_ids' => $node->difficulty_ids ?? [],
                        'current_difficulty_id' => $currentDifficultyId,
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
                'current_difficulty_id' => $this->resolveCurrentDifficultyId($playerProfile, $node->node_id, $node->difficulty_ids ?? []),
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
            'current_difficulty_id' => $this->resolveCurrentDifficultyId($playerProfile, $node->node_id, $node->difficulty_ids ?? []),
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
     * 结算后定位新的当前主线节点：
     * 先留在当前节点推进下一难度，再切到下一节点，最后才尝试切到下一章节。
     *
     * @return array{current_chapter_id:string,current_node_id:string}
     */
    public function resolveNextCurrentLocation(PlayerProfile $playerProfile, string $nodeId, string $difficultyId): array
    {
        $profile = $this->playerRuntimeRepository->refreshProfile($playerProfile);
        $progress = $this->playerRuntimeRepository->getStageProgress($profile->player_id);
        $node = MainlineNode::query()->with('chapter')->where('node_id', $nodeId)->first();

        if (! $node || ! $node->chapter) {
            return [
                'current_chapter_id' => $profile->current_chapter_id ?: '',
                'current_node_id' => $profile->current_node_id ?: '',
            ];
        }

        $difficultyIds = collect($node->difficulty_ids ?? [])
            ->filter()
            ->values();
        $currentIndex = $difficultyIds->search($difficultyId);

        if (is_int($currentIndex) && $currentIndex >= 0 && $currentIndex < ($difficultyIds->count() - 1)) {
            return [
                'current_chapter_id' => $node->chapter_id,
                'current_node_id' => $node->node_id,
            ];
        }

        $orderedNodes = MainlineNode::query()
            ->where('chapter_id', $node->chapter_id)
            ->orderBy('node_id')
            ->get()
            ->values();
        $nodeIndex = $orderedNodes->search(static fn (MainlineNode $entry): bool => $entry->node_id === $node->node_id);
        $chapterUnlocked = $this->isChapterUnlocked($profile, $node->chapter, $progress);

        if (is_int($nodeIndex)) {
            $nextNode = $orderedNodes->get($nodeIndex + 1);

            if (
                $nextNode instanceof MainlineNode &&
                $this->isNodeUnlocked($profile, $orderedNodes, $progress, $nextNode, $nodeIndex + 1, $chapterUnlocked)
            ) {
                return [
                    'current_chapter_id' => $nextNode->chapter_id,
                    'current_node_id' => $nextNode->node_id,
                ];
            }
        }

        $nextChapter = MainlineChapter::query()
            ->orderBy('sort_order')
            ->orderBy('chapter_id')
            ->get()
            ->first(function (MainlineChapter $chapter) use ($node): bool {
                return (int) $chapter->sort_order > (int) ($node->chapter->sort_order ?? 0);
            });

        if ($nextChapter instanceof MainlineChapter && $this->isChapterUnlocked($profile, $nextChapter, $progress)) {
            $firstNode = MainlineNode::query()
                ->where('chapter_id', $nextChapter->chapter_id)
                ->orderBy('node_id')
                ->first();

            if ($firstNode instanceof MainlineNode) {
                return [
                    'current_chapter_id' => $nextChapter->chapter_id,
                    'current_node_id' => $firstNode->node_id,
                ];
            }
        }

        return [
            'current_chapter_id' => $node->chapter_id,
            'current_node_id' => $node->node_id,
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
        $chapterUnlocked = $this->isChapterUnlocked($playerProfile, $chapter, $progress);
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
            ->orderBy('difficulty_order')
            ->orderBy('difficulty_id')
            ->get()
            ->mapWithKeys(static fn (MainlineDifficulty $difficulty): array => [
                $difficulty->difficulty_id => $difficulty,
            ]);
        $orderedDifficultyIds = collect($node->difficulty_ids ?? [])
            ->filter()
            ->values();

        if ($orderedDifficultyIds->isEmpty()) {
            $orderedDifficultyIds = $difficultyMap
                ->sortBy([
                    ['difficulty_order', 'asc'],
                    ['difficulty_id', 'asc'],
                ])
                ->keys()
                ->values();
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

            $isUnlocked = $nodeUnlocked && $previousCleared;
            $currentDifficultyId = $this->resolveCurrentDifficultyId($playerProfile, $nodeId, $orderedDifficultyIds->all());

            $difficulties[] = [
                'difficulty_id' => $difficulty->difficulty_id,
                'node_id' => $difficulty->node_id,
                'difficulty_order' => (int) $difficulty->difficulty_order,
                'difficulty_name' => $difficulty->difficulty_name ?: MainlineDifficulty::defaultDifficultyName($difficulty->difficulty_id),
                'recommended_power' => (int) $difficulty->recommended_power,
                'first_clear_reward_group_id' => $difficulty->first_clear_reward_group_id,
                'is_unlocked' => $isUnlocked,
                'is_first_clear' => (bool) ($progressRecord?->is_first_clear ?? false),
                'clear_count' => (int) ($progressRecord?->clear_count ?? 0),
                'progress_state' => $this->resolveDifficultyProgressState(
                    $isUnlocked,
                    (int) ($progressRecord?->clear_count ?? 0),
                    $playerProfile->current_node_id === $nodeId && $difficulty->difficulty_id === $currentDifficultyId,
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

        if ((int) $playerProfile->level < $levelRequirement) {
            return false;
        }

        $clearNodeId = (string) (($node->unlock_condition ?? [])['clear_node_id'] ?? (($node->unlock_condition ?? [])['conditions']['clear_node_id'] ?? ''));

        if ($clearNodeId !== '') {
            $requiredClear = $progress
                ->first(fn ($entry): bool => $entry->node_id === $clearNodeId && (int) $entry->clear_count > 0);

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
            ->first(fn ($entry): bool => $entry->node_id === $previousNode->node_id && (int) $entry->clear_count > 0);

        return $previousCleared !== null || $playerProfile->current_node_id === $node->node_id;
    }

    private function isChapterUnlocked(PlayerProfile $playerProfile, MainlineChapter $chapter, Collection $progress): bool
    {
        if ((int) $playerProfile->level < (int) $chapter->unlock_level) {
            return false;
        }

        if (blank($chapter->required_previous_chapter)) {
            return true;
        }

        $completionNode = $this->resolveChapterCompletionNode((string) $chapter->required_previous_chapter);

        if (! $completionNode instanceof MainlineNode) {
            return false;
        }

        $requiredDifficultyId = $this->resolveChapterRequiredDifficultyId($chapter, $completionNode);

        if ($requiredDifficultyId === '') {
            return false;
        }

        return $progress->contains(function ($progressRecord) use ($completionNode, $requiredDifficultyId): bool {
            return $progressRecord->node_id === $completionNode->node_id
                && $progressRecord->difficulty_id === $requiredDifficultyId
                && (int) $progressRecord->clear_count > 0;
        });
    }

    private function resolveChapterCompletionNode(string $chapterId): ?MainlineNode
    {
        return MainlineNode::query()
            ->where('chapter_id', $chapterId)
            ->orderBy('node_id', 'desc')
            ->first();
    }

    private function resolveChapterRequiredDifficultyId(MainlineChapter $chapter, MainlineNode $completionNode): string
    {
        $configuredDifficultyId = (string) ($chapter->required_previous_highest_difficulty ?? '');

        if ($configuredDifficultyId !== '') {
            return $configuredDifficultyId;
        }

        $highestDifficulty = MainlineDifficulty::query()
            ->where('node_id', $completionNode->node_id)
            ->orderByDesc('difficulty_order')
            ->orderByDesc('difficulty_id')
            ->first();

        return (string) ($highestDifficulty?->difficulty_id ?? '');
    }

    /**
     * @param  list<string>  $difficultyIds
     */
    private function resolveCurrentDifficultyId(PlayerProfile $playerProfile, string $nodeId, array $difficultyIds): string
    {
        foreach ($difficultyIds as $difficultyId) {
            $progress = $this->playerRuntimeRepository->findStageProgress(
                $playerProfile->player_id,
                $nodeId,
                (string) $difficultyId,
            );

            if ((int) ($progress?->clear_count ?? 0) <= 0) {
                return (string) $difficultyId;
            }
        }

        return (string) ($difficultyIds[count($difficultyIds) - 1] ?? '');
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
