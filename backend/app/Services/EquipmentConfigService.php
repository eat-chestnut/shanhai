<?php

namespace App\Services;

use App\Models\BlueAffix;
use App\Models\Equipment;
use App\Models\EquipmentSet;
use App\Models\Gem;
use App\Models\PurpleRefinement;
use App\Repositories\Contracts\EquipmentConfigRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use JsonException;

class EquipmentConfigService
{
    public function __construct(
        private readonly EquipmentConfigRepositoryInterface $repository,
    ) {}

    /**
     * @return array{equipment:int,sets:int,gems:int,blue_affixes:int,purple_refinements:int}
     */
    public function importFromJson(string $path): array
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("Equipment config JSON file not found: {$path}");
        }

        try {
            $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Equipment config JSON is invalid.', previous: $exception);
        }

        if (! is_array($payload)) {
            throw new InvalidArgumentException('Equipment config payload must be a JSON object.');
        }

        return $this->importFromArray($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{equipment:int,sets:int,gems:int,blue_affixes:int,purple_refinements:int}
     */
    public function importFromArray(array $payload): array
    {
        $validator = Validator::make(
            $payload,
            [
                'equipment_config' => ['required', 'array', 'min:1'],
                'equipment_config.*.equip_id' => ['required', 'string', 'max:100', 'distinct'],
                'equipment_config.*.name' => ['required', 'string', 'max:100'],
                'equipment_config.*.type' => ['required', 'string', 'max:100'],
                'equipment_config.*.level' => ['required', 'integer', 'min:1'],
                'equipment_config.*.base_atk' => ['nullable', 'integer', 'min:0'],
                'equipment_config.*.base_def' => ['nullable', 'integer', 'min:0'],
                'equipment_set_config' => ['nullable', 'array'],
                'equipment_set_config.*.set_id' => ['required', 'string', 'max:100', 'distinct'],
                'equipment_set_config.*.level' => ['required', 'integer', 'min:1'],
                'equipment_set_config.*.pieces' => ['required', 'array', 'min:1'],
                'equipment_set_config.*.pieces.*' => ['required', 'string', 'max:100'],
                'equipment_set_config.*.effects' => ['required', 'array', 'min:1'],
                'equipment_set_config.*.effects.*.count' => ['required', 'integer', 'min:1'],
                'equipment_set_config.*.effects.*.bonus_atk' => ['nullable', 'integer'],
                'equipment_set_config.*.effects.*.bonus_def' => ['nullable', 'integer'],
                'equipment_set_config.*.effects.*.bonus_hp' => ['nullable', 'integer'],
                'equipment_set_config.*.effects.*.bonus_boss_dmg' => ['nullable', 'integer'],
                'gem_config' => ['nullable', 'array'],
                'gem_config.*.gem_id' => ['required', 'string', 'max:100', 'distinct'],
                'gem_config.*.name' => ['required', 'string', 'max:100'],
                'gem_config.*.type' => ['required', 'string', 'max:100'],
                'gem_config.*.bonus_atk' => ['nullable', 'integer'],
                'gem_config.*.bonus_boss_dmg' => ['nullable', 'integer'],
                'blue_affix_config' => ['nullable', 'array'],
                'blue_affix_config.*.affix_id' => ['required', 'string', 'max:100', 'distinct'],
                'blue_affix_config.*.name' => ['required', 'string', 'max:100'],
                'blue_affix_config.*.bonuses' => ['nullable', 'array'],
                'purple_refinement_config' => ['nullable', 'array'],
                'purple_refinement_config.*.refinement_id' => ['required', 'string', 'max:100', 'distinct'],
                'purple_refinement_config.*.name' => ['required', 'string', 'max:100'],
                'purple_refinement_config.*.bonuses' => ['nullable', 'array'],
            ],
        );

        $validated = $validator->validate();
        $equipmentRows = $validated['equipment_config'];
        $setRows = $validated['equipment_set_config'] ?? [];
        $gemRows = $validated['gem_config'] ?? [];
        $blueAffixRows = $validated['blue_affix_config'] ?? [];
        $purpleRefinementRows = $validated['purple_refinement_config'] ?? [];
        $timestamp = Carbon::now();
        $equipmentInsertRows = array_map(
            static fn (array $equipment): array => [
                'equip_id' => $equipment['equip_id'],
                'name' => $equipment['name'],
                'type' => $equipment['type'],
                'level' => (int) $equipment['level'],
                'base_atk' => (int) ($equipment['base_atk'] ?? 0),
                'base_def' => (int) ($equipment['base_def'] ?? 0),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            $equipmentRows,
        );
        $setInsertRows = array_map(
            function (array $set) use ($timestamp): array {
                return [
                    'set_id' => $set['set_id'],
                    'level' => (int) $set['level'],
                    'pieces' => json_encode(
                        array_values(array_unique($set['pieces'])),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                    ),
                    'effects' => json_encode(
                        array_values(array_map([$this, 'normalizeSetEffect'], $set['effects'])),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                    ),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            },
            $setRows,
        );
        $gemInsertRows = array_map(
            static fn (array $gem): array => [
                'gem_id' => $gem['gem_id'],
                'name' => $gem['name'],
                'type' => $gem['type'],
                'bonus_atk' => (int) ($gem['bonus_atk'] ?? 0),
                'bonus_boss_dmg' => (int) ($gem['bonus_boss_dmg'] ?? 0),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            $gemRows,
        );
        $blueAffixInsertRows = array_map(
            function (array $affix) use ($timestamp): array {
                return [
                    'affix_id' => $affix['affix_id'],
                    'name' => $affix['name'],
                    'bonuses' => json_encode(
                        $this->normalizeBonuses($affix['bonuses'] ?? []),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                    ),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            },
            $blueAffixRows,
        );
        $purpleRefinementInsertRows = array_map(
            function (array $refinement) use ($timestamp): array {
                return [
                    'refinement_id' => $refinement['refinement_id'],
                    'name' => $refinement['name'],
                    'bonuses' => json_encode(
                        $this->normalizeBonuses($refinement['bonuses'] ?? []),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                    ),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            },
            $purpleRefinementRows,
        );

        DB::transaction(function () use ($blueAffixInsertRows, $equipmentInsertRows, $gemInsertRows, $purpleRefinementInsertRows, $setInsertRows): void {
            $this->repository->truncateAll();
            $this->repository->insertEquipment($equipmentInsertRows);
            $this->repository->insertSets($setInsertRows);
            $this->repository->insertGems($gemInsertRows);
            $this->repository->insertBlueAffixes($blueAffixInsertRows);
            $this->repository->insertPurpleRefinements($purpleRefinementInsertRows);
        });

        return [
            'equipment' => count($equipmentRows),
            'sets' => count($setRows),
            'gems' => count($gemRows),
            'blue_affixes' => count($blueAffixRows),
            'purple_refinements' => count($purpleRefinementRows),
        ];
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function exportToArray(): array
    {
        return [
            'equipment_config' => $this->repository->getOrderedEquipment()
                ->map(static fn (Equipment $equipment): array => [
                    'equip_id' => $equipment->equip_id,
                    'name' => $equipment->name,
                    'type' => $equipment->type,
                    'level' => (int) $equipment->level,
                    'base_atk' => (int) $equipment->base_atk,
                    'base_def' => (int) $equipment->base_def,
                ])
                ->all(),
            'equipment_set_config' => $this->repository->getOrderedSets()
                ->map(function (EquipmentSet $set): array {
                    return [
                        'set_id' => $set->set_id,
                        'level' => (int) $set->level,
                        'pieces' => $set->pieces ?? [],
                        'effects' => array_values(array_map([$this, 'normalizeSetEffect'], $set->effects ?? [])),
                    ];
                })
                ->all(),
            'gem_config' => $this->repository->getOrderedGems()
                ->map(static fn (Gem $gem): array => [
                    'gem_id' => $gem->gem_id,
                    'name' => $gem->name,
                    'type' => $gem->type,
                    'bonus_atk' => (int) $gem->bonus_atk,
                    'bonus_boss_dmg' => (int) $gem->bonus_boss_dmg,
                ])
                ->all(),
            'blue_affix_config' => $this->repository->getOrderedBlueAffixes()
                ->map(function (BlueAffix $affix): array {
                    return [
                        'affix_id' => $affix->affix_id,
                        'name' => $affix->name,
                        'bonuses' => $this->normalizeBonuses($affix->bonuses ?? []),
                    ];
                })
                ->all(),
            'purple_refinement_config' => $this->repository->getOrderedPurpleRefinements()
                ->map(function (PurpleRefinement $refinement): array {
                    return [
                        'refinement_id' => $refinement->refinement_id,
                        'name' => $refinement->name,
                        'bonuses' => $this->normalizeBonuses($refinement->bonuses ?? []),
                    ];
                })
                ->all(),
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
                'path' => '装备配置导出失败。',
            ]);
        }

        file_put_contents($path, $json.PHP_EOL);

        return $path;
    }

    /**
     * @param  array<string, mixed>  $bonuses
     * @return array<string, mixed>
     */
    private function normalizeBonuses(array $bonuses): array
    {
        $normalized = [];

        foreach ($bonuses as $key => $value) {
            if ($key === '' || $value === null || $value === '') {
                continue;
            }

            $normalized[(string) $key] = is_numeric($value) ? (int) $value : $value;
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $effect
     * @return array<string, int>
     */
    private function normalizeSetEffect(array $effect): array
    {
        $normalized = [
            'count' => (int) ($effect['count'] ?? 0),
        ];

        foreach (['bonus_atk', 'bonus_def', 'bonus_hp', 'bonus_boss_dmg'] as $field) {
            $value = $effect[$field] ?? null;

            if ($value !== null && $value !== '') {
                $normalized[$field] = (int) $value;
            }
        }

        return $normalized;
    }
}
