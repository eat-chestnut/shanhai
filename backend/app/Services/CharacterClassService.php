<?php

namespace App\Services;

use App\Enums\RoleType;
use App\Models\CharacterClass;
use App\Repositories\Contracts\CharacterClassRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use JsonException;

class CharacterClassService
{
    public function __construct(
        private readonly CharacterClassRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function create(array $data): CharacterClass
    {
        return $this->repository->create($data);
    }

    public function update(CharacterClass $characterClass, array $data): CharacterClass
    {
        return $this->repository->update($characterClass, $data);
    }

    public function delete(CharacterClass $characterClass): bool
    {
        return $this->repository->delete($characterClass);
    }

    public function syncFromJson(string $path): int
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("Class config JSON file not found: {$path}");
        }

        try {
            $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Class config JSON is invalid.', previous: $exception);
        }

        $rows = $payload['class_config'] ?? null;

        $validator = Validator::make(
            ['class_config' => $rows],
            [
                'class_config' => ['required', 'array', 'min:1'],
                'class_config.*.class_id' => ['required', 'string', 'max:100'],
                'class_config.*.class_name' => ['required', 'string', 'max:100'],
                'class_config.*.class_desc' => ['nullable', 'string'],
                'class_config.*.role_type' => ['required', 'string', Rule::in(RoleType::values())],
                'class_config.*.is_open' => ['required', 'boolean'],
            ],
        );

        $validated = $validator->validate()['class_config'];
        $timestamp = Carbon::now();

        $rows = array_map(
            static fn (array $row): array => [
                'class_id' => $row['class_id'],
                'class_name' => $row['class_name'],
                'class_desc' => $row['class_desc'] ?? null,
                'role_type' => $row['role_type'],
                'is_open' => (bool) $row['is_open'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            $validated,
        );

        $this->repository->upsertByClassId($rows);

        return count($rows);
    }
}
