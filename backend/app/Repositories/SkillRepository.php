<?php

namespace App\Repositories;

use App\Models\Skill;
use App\Repositories\Contracts\SkillRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SkillRepository implements SkillRepositoryInterface
{
    /**
     * @var list<string>
     */
    private const ALLOWED_SORT_COLUMNS = [
        'skill_id',
        'class_id',
        'skill_name',
        'type',
        'effect_type',
        'target_type',
        'cooldown',
        'cost',
        'unlock_level',
        'max_level',
        'created_at',
        'updated_at',
    ];

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Skill::query()->with('characterClass');

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
            ->orderBy('skill_id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Skill
    {
        return Skill::query()->create($data);
    }

    public function update(Skill $skill, array $data): Skill
    {
        $skill->update($data);

        return $skill->refresh();
    }

    public function delete(Skill $skill): bool
    {
        return (bool) $skill->delete();
    }

    public function upsertBySkillId(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        Skill::query()->upsert(
            $rows,
            ['skill_id'],
            [
                'class_id',
                'skill_name',
                'skill_desc',
                'type',
                'effect_type',
                'target_type',
                'cooldown',
                'cost',
                'unlock_level',
                'max_level',
                'power_base',
                'power_per_level',
                'duration',
                'chance',
                'stat_bonuses',
                'effect_payload',
                'is_open',
                'updated_at',
            ],
        );
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('skill_id', 'like', "%{$search}%")
                    ->orWhere('class_id', 'like', "%{$search}%")
                    ->orWhere('skill_name', 'like', "%{$search}%")
                    ->orWhere('skill_desc', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('effect_type', 'like', "%{$search}%");
            });
        }

        $classId = trim((string) ($filters['class_id'] ?? ''));

        if ($classId !== '') {
            $query->where('class_id', $classId);
        }

        $type = trim((string) ($filters['type'] ?? ''));

        if ($type !== '') {
            $query->where('type', $type);
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
