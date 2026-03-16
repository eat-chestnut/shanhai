<?php

namespace App\Services;

use App\Enums\SkillEffectType;
use App\Enums\SkillTargetType;
use App\Enums\SkillType;
use App\Models\Skill;
use App\Repositories\Contracts\SkillRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use JsonException;

class SkillService
{
    public function __construct(
        private readonly SkillRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function create(array $data): Skill
    {
        return $this->repository->create($data);
    }

    public function update(Skill $skill, array $data): Skill
    {
        return $this->repository->update($skill, $data);
    }

    public function delete(Skill $skill): bool
    {
        return $this->repository->delete($skill);
    }

    public function syncFromJson(string $path): int
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("Skill config JSON file not found: {$path}");
        }

        try {
            $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Skill config JSON is invalid.', previous: $exception);
        }

        $rows = $payload['skills'] ?? null;

        $validator = Validator::make(
            ['skills' => $rows],
            [
                'skills' => ['required', 'array', 'min:1'],
                'skills.*.skill_id' => ['required', 'string', 'max:100'],
                'skills.*.class_id' => ['required', 'string', 'max:100', Rule::exists('character_classes', 'class_id')],
                'skills.*.skill_name' => ['nullable', 'string', 'max:100'],
                'skills.*.skill_desc' => ['nullable', 'string'],
                'skills.*.type' => ['required', 'string', Rule::in(SkillType::values())],
                'skills.*.effect_type' => ['nullable', 'string', Rule::in(SkillEffectType::values())],
                'skills.*.target_type' => ['nullable', 'string', Rule::in(SkillTargetType::values())],
                'skills.*.range' => ['nullable', 'string', Rule::in(SkillTargetType::values())],
                'skills.*.cooldown' => ['nullable', 'integer', 'min:0'],
                'skills.*.cost' => ['nullable', 'integer', 'min:0'],
                'skills.*.unlock_level' => ['nullable', 'integer', 'min:1'],
                'skills.*.max_level' => ['nullable', 'integer', 'min:1'],
                'skills.*.power_base' => ['nullable', 'integer', 'min:0'],
                'skills.*.power_per_level' => ['nullable', 'integer', 'min:0'],
                'skills.*.damage' => ['nullable', 'integer', 'min:0'],
                'skills.*.duration' => ['nullable', 'integer', 'min:0'],
                'skills.*.chance' => ['nullable', 'numeric', 'between:0,1'],
                'skills.*.stat_bonuses' => ['nullable', 'array'],
                'skills.*.effect_payload' => ['nullable', 'array'],
                'skills.*.is_open' => ['nullable', 'boolean'],
                'skills.*.effect' => ['nullable', 'string'],
                'skills.*.bonus_atk' => ['nullable', 'integer'],
                'skills.*.bonus_def' => ['nullable', 'integer'],
                'skills.*.bonus_hp' => ['nullable', 'integer'],
                'skills.*.bonus_boss_dmg' => ['nullable', 'integer'],
            ],
        );

        $validated = $validator->validate()['skills'];
        $timestamp = Carbon::now();

        $rows = array_map(
            function (array $row) use ($timestamp): array {
                $statBonuses = $row['stat_bonuses'] ?? [];

                foreach (['bonus_atk', 'bonus_def', 'bonus_hp', 'bonus_boss_dmg'] as $bonusKey) {
                    if (array_key_exists($bonusKey, $row)) {
                        $statBonuses[$bonusKey] = (int) $row[$bonusKey];
                    }
                }

                $effectType = $row['effect_type'] ?? $row['effect'] ?? null;
                $targetType = $row['target_type'] ?? $row['range'] ?? null;

                $statBonusesPayload = $statBonuses === []
                    ? null
                    : json_encode($statBonuses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                $effectPayload = $row['effect_payload'] ?? [];
                $effectPayloadValue = $effectPayload === []
                    ? null
                    : json_encode($effectPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

                return [
                    'skill_id' => $row['skill_id'],
                    'class_id' => $row['class_id'],
                    'skill_name' => $row['skill_name'] ?? $row['skill_id'],
                    'skill_desc' => $row['skill_desc'] ?? null,
                    'type' => $row['type'],
                    'effect_type' => $effectType,
                    'target_type' => $targetType,
                    'cooldown' => (int) ($row['cooldown'] ?? 0),
                    'cost' => (int) ($row['cost'] ?? 0),
                    'unlock_level' => (int) ($row['unlock_level'] ?? 1),
                    'max_level' => (int) ($row['max_level'] ?? 5),
                    'power_base' => (int) ($row['power_base'] ?? $row['damage'] ?? 0),
                    'power_per_level' => (int) ($row['power_per_level'] ?? 0),
                    'duration' => (int) ($row['duration'] ?? 0),
                    'chance' => (float) ($row['chance'] ?? 0),
                    'stat_bonuses' => $statBonusesPayload,
                    'effect_payload' => $effectPayloadValue,
                    'is_open' => (bool) ($row['is_open'] ?? true),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            },
            $validated,
        );

        $this->repository->upsertBySkillId($rows);

        return count($rows);
    }
}
