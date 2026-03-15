<?php

namespace App\Repositories;

use App\Models\CharacterClass;
use App\Repositories\Contracts\CharacterClassRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CharacterClassRepository implements CharacterClassRepositoryInterface
{
    /**
     * @var list<string>
     */
    private const ALLOWED_SORT_COLUMNS = [
        'class_id',
        'class_name',
        'role_type',
        'is_open',
        'created_at',
        'updated_at',
    ];

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = CharacterClass::query();

        $this->applyFilters($query, $filters);

        $requestedSortBy = $filters['sort_by'] ?? 'class_id';

        $sortBy = in_array($requestedSortBy, self::ALLOWED_SORT_COLUMNS, true)
            ? $requestedSortBy
            : 'class_id';
        $sortDirection = strtolower((string) ($filters['sort_direction'] ?? 'asc')) === 'desc'
            ? 'desc'
            : 'asc';
        $perPage = max(1, min((int) ($filters['per_page'] ?? 15), 100));

        return $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): CharacterClass
    {
        return CharacterClass::query()->create($data);
    }

    public function update(CharacterClass $characterClass, array $data): CharacterClass
    {
        $characterClass->update($data);

        return $characterClass->refresh();
    }

    public function delete(CharacterClass $characterClass): bool
    {
        return (bool) $characterClass->delete();
    }

    public function upsertByClassId(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        CharacterClass::query()->upsert(
            $rows,
            ['class_id'],
            ['class_name', 'class_desc', 'role_type', 'is_open', 'updated_at'],
        );
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('class_id', 'like', "%{$search}%")
                    ->orWhere('class_name', 'like', "%{$search}%")
                    ->orWhere('class_desc', 'like', "%{$search}%")
                    ->orWhere('role_type', 'like', "%{$search}%");
            });
        }

        $roleType = $filters['role_type'] ?? null;

        if (filled($roleType)) {
            $query->where('role_type', $roleType);
        }

        $isOpen = $this->normalizeBoolean($filters['is_open'] ?? null);

        if ($isOpen !== null) {
            $query->where('is_open', $isOpen);
        }
    }

    private function normalizeBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
