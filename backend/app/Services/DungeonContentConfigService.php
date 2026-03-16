<?php

namespace App\Services;

use App\Enums\MonsterDropKind;
use App\Models\Dungeon;
use App\Models\DungeonDifficulty;
use App\Models\Monster;
use App\Models\MonsterDrop;
use App\Repositories\Contracts\DungeonContentConfigRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use JsonException;

class DungeonContentConfigService
{
    public function __construct(
        private readonly DungeonContentConfigRepositoryInterface $repository,
    ) {}

    /**
     * @return array{dungeons:int,difficulties:int,monsters:int,drops:int}
     */
    public function importFromJson(string $path): array
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("Dungeon content config JSON file not found: {$path}");
        }

        try {
            $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Dungeon content config JSON is invalid.', previous: $exception);
        }

        if (! is_array($payload)) {
            throw new InvalidArgumentException('Dungeon content config payload must be a JSON object.');
        }

        return $this->importFromArray($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{dungeons:int,difficulties:int,monsters:int,drops:int}
     */
    public function importFromArray(array $payload): array
    {
        $validator = Validator::make(
            $payload,
            [
                'dungeon_config' => ['required', 'array', 'min:1'],
                'dungeon_config.*.dungeon_id' => ['required', 'string', 'max:100', 'distinct'],
                'dungeon_config.*.dungeon_name' => ['required', 'string', 'max:100'],
                'dungeon_config.*.unlock_level' => ['required', 'integer', 'min:1'],
                'dungeon_difficulty_config' => ['required', 'array', 'min:1'],
                'dungeon_difficulty_config.*.difficulty_id' => ['required', 'string', 'max:100'],
                'dungeon_difficulty_config.*.dungeon_id' => ['required', 'string', 'max:100'],
                'dungeon_difficulty_config.*.recommended_power' => ['required', 'integer', 'min:0'],
                'dungeon_difficulty_config.*.first_clear_reward_group_id' => ['nullable', 'string', 'max:100'],
                'monster_config' => ['required', 'array', 'min:1'],
                'monster_config.*.monster_id' => ['required', 'string', 'max:100', 'distinct'],
                'monster_config.*.name' => ['required', 'string', 'max:100'],
                'monster_config.*.base_hp' => ['required', 'integer', 'min:0'],
                'monster_config.*.base_atk' => ['required', 'integer', 'min:0'],
                'monster_config.*.is_boss' => ['required', 'boolean'],
                'monster_drop_config' => ['required', 'array', 'min:1'],
                'monster_drop_config.*.monster_id' => ['required', 'string', 'max:100'],
                'monster_drop_config.*.item_id' => ['required', 'string', 'max:100'],
                'monster_drop_config.*.drop_rate' => ['required', 'numeric', 'between:0,1'],
                'monster_drop_config.*.drop_kind' => ['nullable', 'string', Rule::in(MonsterDropKind::values())],
            ],
        );

        $validator->after(function ($validator) use ($payload): void {
            $dungeons = $payload['dungeon_config'] ?? [];
            $difficulties = $payload['dungeon_difficulty_config'] ?? [];
            $monsters = $payload['monster_config'] ?? [];
            $drops = $payload['monster_drop_config'] ?? [];

            $dungeonIds = [];

            foreach ($dungeons as $dungeon) {
                $dungeonIds[(string) ($dungeon['dungeon_id'] ?? '')] = true;
            }

            $difficultyKeys = [];

            foreach ($difficulties as $index => $difficulty) {
                $dungeonId = (string) ($difficulty['dungeon_id'] ?? '');
                $difficultyId = (string) ($difficulty['difficulty_id'] ?? '');
                $compositeKey = "{$dungeonId}::{$difficultyId}";

                if ($dungeonId !== '' && ! isset($dungeonIds[$dungeonId])) {
                    $validator->errors()->add("dungeon_difficulty_config.{$index}.dungeon_id", 'dungeon_id 必须引用已存在的副本。');
                }

                if (isset($difficultyKeys[$compositeKey])) {
                    $validator->errors()->add("dungeon_difficulty_config.{$index}.difficulty_id", '同一副本下 difficulty_id 不能重复。');
                }

                $difficultyKeys[$compositeKey] = true;
            }

            $monsterIsBossMap = [];
            $dropKeys = [];

            foreach ($monsters as $monster) {
                $monsterIsBossMap[(string) ($monster['monster_id'] ?? '')] = (bool) ($monster['is_boss'] ?? false);
            }

            foreach ($drops as $index => $drop) {
                $monsterId = (string) ($drop['monster_id'] ?? '');
                $itemId = (string) ($drop['item_id'] ?? '');
                $compositeKey = "{$monsterId}::{$itemId}";

                if ($monsterId !== '' && ! array_key_exists($monsterId, $monsterIsBossMap)) {
                    $validator->errors()->add("monster_drop_config.{$index}.monster_id", 'monster_id 必须引用已存在的怪物。');

                    continue;
                }

                if (isset($dropKeys[$compositeKey])) {
                    $validator->errors()->add("monster_drop_config.{$index}.item_id", '同一怪物下 item_id 不能重复。');
                }

                $dropKeys[$compositeKey] = true;

                $dropKind = $drop['drop_kind'] ?? null;

                if (
                    $dropKind !== null &&
                    in_array($dropKind, [MonsterDropKind::BossFixed->value, MonsterDropKind::BossCore->value], true) &&
                    ! ($monsterIsBossMap[$monsterId] ?? false)
                ) {
                    $validator->errors()->add("monster_drop_config.{$index}.drop_kind", '只有 Boss 怪物才能配置 Boss 固定掉落或核心掉落。');
                }
            }
        });

        $validated = $validator->validate();
        $monsterBossMap = [];

        foreach ($validated['monster_config'] as $monster) {
            $monsterBossMap[$monster['monster_id']] = (bool) $monster['is_boss'];
        }

        $timestamp = Carbon::now();
        $dungeonRows = array_map(
            static fn (array $dungeon): array => [
                'dungeon_id' => $dungeon['dungeon_id'],
                'dungeon_name' => $dungeon['dungeon_name'],
                'unlock_level' => (int) $dungeon['unlock_level'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            $validated['dungeon_config'],
        );
        $difficultyRows = array_map(
            static fn (array $difficulty): array => [
                'difficulty_id' => $difficulty['difficulty_id'],
                'dungeon_id' => $difficulty['dungeon_id'],
                'recommended_power' => (int) $difficulty['recommended_power'],
                'first_clear_reward_group_id' => $difficulty['first_clear_reward_group_id'] ?? null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            $validated['dungeon_difficulty_config'],
        );
        $monsterRows = array_map(
            static fn (array $monster): array => [
                'monster_id' => $monster['monster_id'],
                'name' => $monster['name'],
                'base_hp' => (int) $monster['base_hp'],
                'base_atk' => (int) $monster['base_atk'],
                'is_boss' => (bool) $monster['is_boss'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            $validated['monster_config'],
        );
        $dropRows = array_map(
            function (array $drop) use ($monsterBossMap, $timestamp): array {
                $monsterId = $drop['monster_id'];
                $dropRate = (float) $drop['drop_rate'];
                $dropKind = $drop['drop_kind']
                    ?? MonsterDropKind::infer(
                        $monsterBossMap[$monsterId] ?? false,
                        $drop['item_id'],
                        $dropRate,
                    )->value;

                return [
                    'monster_id' => $monsterId,
                    'item_id' => $drop['item_id'],
                    'drop_rate' => $dropRate,
                    'drop_kind' => $dropKind,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            },
            $validated['monster_drop_config'],
        );

        DB::transaction(function () use ($difficultyRows, $dropRows, $dungeonRows, $monsterRows): void {
            $this->repository->truncateAll();
            $this->repository->insertDungeons($dungeonRows);
            $this->repository->insertDifficulties($difficultyRows);
            $this->repository->insertMonsters($monsterRows);
            $this->repository->insertDrops($dropRows);
        });

        return [
            'dungeons' => count($validated['dungeon_config']),
            'difficulties' => count($validated['dungeon_difficulty_config']),
            'monsters' => count($validated['monster_config']),
            'drops' => count($validated['monster_drop_config']),
        ];
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function exportToArray(): array
    {
        $dungeons = $this->repository->getOrderedDungeons()
            ->map(static fn (Dungeon $dungeon): array => [
                'dungeon_id' => $dungeon->dungeon_id,
                'dungeon_name' => $dungeon->dungeon_name,
                'unlock_level' => (int) $dungeon->unlock_level,
            ])
            ->all();

        $difficulties = $this->repository->getOrderedDifficulties()
            ->map(static fn (DungeonDifficulty $difficulty): array => [
                'difficulty_id' => $difficulty->difficulty_id,
                'dungeon_id' => $difficulty->dungeon_id,
                'recommended_power' => (int) $difficulty->recommended_power,
                'first_clear_reward_group_id' => $difficulty->first_clear_reward_group_id,
            ])
            ->all();

        $monsters = $this->repository->getOrderedMonsters()
            ->map(static fn (Monster $monster): array => [
                'monster_id' => $monster->monster_id,
                'name' => $monster->name,
                'base_hp' => (int) $monster->base_hp,
                'base_atk' => (int) $monster->base_atk,
                'is_boss' => (bool) $monster->is_boss,
            ])
            ->all();

        $drops = $this->repository->getOrderedDrops()
            ->map(static function (MonsterDrop $drop): array {
                $payload = [
                    'monster_id' => $drop->monster_id,
                    'item_id' => $drop->item_id,
                    'drop_rate' => round((float) $drop->drop_rate, 4),
                ];

                if ($drop->drop_kind !== MonsterDropKind::Normal->value) {
                    $payload['drop_kind'] = $drop->drop_kind;
                }

                return $payload;
            })
            ->all();

        return [
            'dungeon_config' => $dungeons,
            'dungeon_difficulty_config' => $difficulties,
            'monster_config' => $monsters,
            'monster_drop_config' => $drops,
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
                'path' => '副本配置导出失败。',
            ]);
        }

        file_put_contents($path, $json.PHP_EOL);

        return $path;
    }
}
