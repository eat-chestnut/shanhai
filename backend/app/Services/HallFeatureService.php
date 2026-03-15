<?php

namespace App\Services;

use App\Models\HallFeature;
use App\Repositories\Contracts\HallFeatureRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use JsonException;

class HallFeatureService
{
    public function __construct(
        private readonly HallFeatureRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function create(array $data): HallFeature
    {
        return $this->repository->create($this->preparePayload($data));
    }

    public function update(HallFeature $hallFeature, array $data): HallFeature
    {
        return $this->repository->update($hallFeature, $this->preparePayload($data));
    }

    public function delete(HallFeature $hallFeature): bool
    {
        return $this->repository->delete($hallFeature);
    }

    public function syncFromJson(string $path): int
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("Hall feature config JSON file not found: {$path}");
        }

        try {
            $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Hall feature config JSON is invalid.', previous: $exception);
        }

        $rows = $payload['hall_feature_config'] ?? null;

        $validator = Validator::make(
            ['hall_feature_config' => $rows],
            [
                'hall_feature_config' => ['required', 'array', 'min:1'],
                'hall_feature_config.*.feature_id' => ['required', 'string', 'max:100'],
                'hall_feature_config.*.feature_name' => ['required', 'string', 'max:100'],
                'hall_feature_config.*.feature_type' => ['required', 'string', 'max:100'],
                'hall_feature_config.*.unlock_condition' => ['required', 'array'],
                'hall_feature_config.*.unlock_condition.level' => ['required', 'integer', 'min:1'],
                'hall_feature_config.*.jump_target' => ['required', 'array'],
                'hall_feature_config.*.jump_target.page' => ['required', 'string', 'max:100'],
            ],
        );

        $validated = $validator->validate()['hall_feature_config'];
        $timestamp = Carbon::now();

        $rows = array_map(function (array $row) use ($timestamp): array {
            $payload = $this->preparePayload($row);

            return [
                ...$payload,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $validated);

        $this->repository->upsertByFeatureId($rows);

        return count($rows);
    }

    private function preparePayload(array $data): array
    {
        return [
            'feature_id' => $data['feature_id'],
            'feature_name' => $data['feature_name'],
            'feature_type' => $data['feature_type'],
            'unlock_condition' => $this->normalizeUnlockCondition($data['unlock_condition'] ?? []),
            'jump_target' => $this->normalizeJumpTarget($data['jump_target'] ?? []),
        ];
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

    /**
     * @param  array<string, mixed>  $jumpTarget
     * @return array<string, mixed>
     */
    private function normalizeJumpTarget(array $jumpTarget): array
    {
        $normalized = [
            'page' => (string) ($jumpTarget['page'] ?? ''),
        ];

        if (isset($jumpTarget['params']) && is_array($jumpTarget['params']) && $jumpTarget['params'] !== []) {
            $normalized['params'] = $jumpTarget['params'];
        }

        return $normalized;
    }
}
