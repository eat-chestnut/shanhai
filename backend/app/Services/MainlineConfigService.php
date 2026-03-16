<?php

namespace App\Services;

use App\Models\MainlineChapter;
use App\Models\MainlineDifficulty;
use App\Models\MainlineNode;
use App\Repositories\Contracts\MainlineConfigRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use JsonException;

class MainlineConfigService
{
    public function __construct(
        private readonly MainlineConfigRepositoryInterface $repository,
    ) {}

    /**
     * @return array{chapters:int,nodes:int,difficulties:int}
     */
    public function importFromJson(string $path): array
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("Mainline config JSON file not found: {$path}");
        }

        try {
            $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Mainline config JSON is invalid.', previous: $exception);
        }

        if (! is_array($payload)) {
            throw new InvalidArgumentException('Mainline config payload must be a JSON object.');
        }

        return $this->importFromArray($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{chapters:int,nodes:int,difficulties:int}
     */
    public function importFromArray(array $payload): array
    {
        $validator = Validator::make(
            $payload,
            [
                'chapter_config' => ['required', 'array', 'min:1'],
                'chapter_config.*.chapter_id' => ['required', 'string', 'max:100', 'distinct'],
                'chapter_config.*.chapter_name' => ['required', 'string', 'max:100'],
                'chapter_config.*.unlock_level' => ['required', 'integer', 'min:1'],
                'node_config' => ['required', 'array', 'min:1'],
                'node_config.*.node_id' => ['required', 'string', 'max:100', 'distinct'],
                'node_config.*.chapter_id' => ['required', 'string', 'max:100'],
                'node_config.*.node_name' => ['required', 'string', 'max:100'],
                'node_config.*.unlock_condition' => ['required', 'array'],
                'node_config.*.unlock_condition.level' => ['required', 'integer', 'min:1'],
                'node_config.*.unlock_condition.conditions' => ['nullable', 'array'],
                'node_config.*.difficulty_ids' => ['required', 'array', 'min:1'],
                'node_config.*.difficulty_ids.*' => ['required', 'string', 'max:100'],
                'difficulty_config' => ['required', 'array', 'min:1'],
                'difficulty_config.*.difficulty_id' => ['required', 'string', 'max:100'],
                'difficulty_config.*.node_id' => ['required', 'string', 'max:100'],
                'difficulty_config.*.recommended_power' => ['required', 'integer', 'min:0'],
                'difficulty_config.*.first_clear_reward_group_id' => ['nullable', 'string', 'max:100'],
            ],
        );

        $validator->after(function ($validator) use ($payload): void {
            $chapters = $payload['chapter_config'] ?? [];
            $nodes = $payload['node_config'] ?? [];
            $difficulties = $payload['difficulty_config'] ?? [];

            $chapterIds = [];

            foreach ($chapters as $chapter) {
                $chapterIds[(string) ($chapter['chapter_id'] ?? '')] = true;
            }

            $nodeIds = [];
            $expectedDifficultyIdsByNode = [];

            foreach ($nodes as $index => $node) {
                $chapterId = (string) ($node['chapter_id'] ?? '');
                $nodeId = (string) ($node['node_id'] ?? '');

                if ($chapterId !== '' && ! isset($chapterIds[$chapterId])) {
                    $validator->errors()->add("node_config.{$index}.chapter_id", 'chapter_id 必须引用已存在的章节。');
                }

                $nodeIds[$nodeId] = true;
                $expectedDifficultyIdsByNode[$nodeId] = array_values(array_unique($node['difficulty_ids'] ?? []));
            }

            $difficultyIdsByNode = [];
            $difficultyKeys = [];

            foreach ($difficulties as $index => $difficulty) {
                $nodeId = (string) ($difficulty['node_id'] ?? '');
                $difficultyId = (string) ($difficulty['difficulty_id'] ?? '');
                $compositeKey = "{$nodeId}::{$difficultyId}";

                if ($nodeId !== '' && ! isset($nodeIds[$nodeId])) {
                    $validator->errors()->add("difficulty_config.{$index}.node_id", 'node_id 必须引用已存在的节点。');
                }

                if (isset($difficultyKeys[$compositeKey])) {
                    $validator->errors()->add("difficulty_config.{$index}.difficulty_id", '同一节点下 difficulty_id 不能重复。');
                }

                $difficultyKeys[$compositeKey] = true;
                $difficultyIdsByNode[$nodeId][] = $difficultyId;
            }

            foreach ($expectedDifficultyIdsByNode as $nodeId => $difficultyIds) {
                $expected = $difficultyIds;
                $actual = array_values(array_unique($difficultyIdsByNode[$nodeId] ?? []));
                sort($expected);
                sort($actual);

                if ($expected !== $actual) {
                    $validator->errors()->add("node_config.{$nodeId}.difficulty_ids", 'difficulty_ids 必须与 difficulty_config 中该节点的难度集合一致。');
                }
            }
        });

        $validated = $validator->validate();
        $timestamp = Carbon::now();
        $chapterRows = array_map(
            static fn (array $chapter): array => [
                'chapter_id' => $chapter['chapter_id'],
                'chapter_name' => $chapter['chapter_name'],
                'unlock_level' => (int) $chapter['unlock_level'],
                'sort_order' => isset($chapter['sort_order']) ? (int) $chapter['sort_order'] : 0,
                'required_previous_chapter' => $chapter['required_previous_chapter'] ?? null,
                'required_previous_highest_difficulty' => $chapter['required_previous_highest_difficulty'] ?? null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            $validated['chapter_config'],
        );
        $nodeRows = array_map(
            function (array $node) use ($timestamp): array {
                return [
                    'node_id' => $node['node_id'],
                    'chapter_id' => $node['chapter_id'],
                    'node_name' => $node['node_name'],
                    'unlock_condition' => json_encode(
                        $this->normalizeUnlockCondition($node['unlock_condition']),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                    ),
                    'difficulty_ids' => json_encode(
                        array_values(array_unique($node['difficulty_ids'])),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                    ),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            },
            $validated['node_config'],
        );
        $difficultyRows = array_map(
            static fn (array $difficulty): array => [
                'difficulty_id' => $difficulty['difficulty_id'],
                'node_id' => $difficulty['node_id'],
                'recommended_power' => (int) $difficulty['recommended_power'],
                'first_clear_reward_group_id' => $difficulty['first_clear_reward_group_id'] ?? null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            $validated['difficulty_config'],
        );

        DB::transaction(function () use ($chapterRows, $difficultyRows, $nodeRows): void {
            $this->repository->truncateAll();
            $this->repository->insertChapters($chapterRows);
            $this->repository->insertNodes($nodeRows);
            $this->repository->insertDifficulties($difficultyRows);
        });

        $this->repository->syncDifficultyIds();

        return [
            'chapters' => count($validated['chapter_config']),
            'nodes' => count($validated['node_config']),
            'difficulties' => count($validated['difficulty_config']),
        ];
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function exportToArray(): array
    {
        $this->repository->syncDifficultyIds();

        $chapters = $this->repository->getOrderedChapters()
            ->map(static fn (MainlineChapter $chapter): array => [
                'chapter_id' => $chapter->chapter_id,
                'chapter_name' => $chapter->chapter_name,
                'unlock_level' => (int) $chapter->unlock_level,
            ])
            ->all();

        $nodes = $this->repository->getOrderedNodes()
            ->map(static fn (MainlineNode $node): array => [
                'node_id' => $node->node_id,
                'chapter_id' => $node->chapter_id,
                'node_name' => $node->node_name,
                'unlock_condition' => $node->unlock_condition ?? [],
                'difficulty_ids' => $node->difficulty_ids ?? [],
            ])
            ->all();

        $difficultyOrderMap = $this->repository->getAllNodes()
            ->mapWithKeys(static fn (MainlineNode $node): array => [
                $node->node_id => array_flip($node->difficulty_ids ?? []),
            ]);

        $difficulties = $this->repository->getAllDifficulties()
            ->sortBy(static function (MainlineDifficulty $difficulty) use ($difficultyOrderMap): string {
                $position = $difficultyOrderMap[$difficulty->node_id][$difficulty->difficulty_id] ?? 9999;

                return $difficulty->node_id.'-'.str_pad((string) $position, 4, '0', STR_PAD_LEFT).'-'.$difficulty->difficulty_id;
            })
            ->values()
            ->map(static fn (MainlineDifficulty $difficulty): array => [
                'difficulty_id' => $difficulty->difficulty_id,
                'node_id' => $difficulty->node_id,
                'recommended_power' => (int) $difficulty->recommended_power,
                'first_clear_reward_group_id' => $difficulty->first_clear_reward_group_id,
            ])
            ->all();

        return [
            'chapter_config' => $chapters,
            'node_config' => $nodes,
            'difficulty_config' => $difficulties,
        ];
    }

    public function exportToJson(string $path): string
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $json = json_encode(
            $this->exportToArray(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );

        if (! is_string($json)) {
            throw ValidationException::withMessages([
                'path' => '主线配置导出失败。',
            ]);
        }

        file_put_contents($path, $json.PHP_EOL);

        return $path;
    }

    /**
     * @param  array<string, mixed>  $unlockCondition
     * @return array<string, mixed>
     */
    private function normalizeUnlockCondition(array $unlockCondition): array
    {
        $normalized = [
            'level' => (int) ($unlockCondition['level'] ?? 1),
        ];

        if (isset($unlockCondition['conditions']) && is_array($unlockCondition['conditions']) && $unlockCondition['conditions'] !== []) {
            $normalized['conditions'] = $unlockCondition['conditions'];
        }

        return $normalized;
    }
}
