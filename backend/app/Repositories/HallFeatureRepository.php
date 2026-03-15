<?php

namespace App\Repositories;

use App\Models\HallFeature;
use App\Repositories\Contracts\HallFeatureRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class HallFeatureRepository implements HallFeatureRepositoryInterface
{
    /**
     * @var list<string>
     */
    private const ALLOWED_SORT_COLUMNS = [
        'feature_id',
        'feature_name',
        'feature_type',
        'created_at',
        'updated_at',
    ];

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = HallFeature::query();

        $this->applyFilters($query, $filters);

        $requestedSortBy = $filters['sort_by'] ?? 'feature_id';
        $sortBy = in_array($requestedSortBy, self::ALLOWED_SORT_COLUMNS, true)
            ? $requestedSortBy
            : 'feature_id';
        $sortDirection = strtolower((string) ($filters['sort_direction'] ?? 'asc')) === 'desc'
            ? 'desc'
            : 'asc';
        $perPage = max(1, min((int) ($filters['per_page'] ?? 15), 100));

        return $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): HallFeature
    {
        return HallFeature::query()->create($data);
    }

    public function update(HallFeature $hallFeature, array $data): HallFeature
    {
        $hallFeature->update($data);

        return $hallFeature->refresh();
    }

    public function delete(HallFeature $hallFeature): bool
    {
        return (bool) $hallFeature->delete();
    }

    public function upsertByFeatureId(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $rows = array_map(static function (array $row): array {
            $row['unlock_condition'] = json_encode($row['unlock_condition'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $row['jump_target'] = json_encode($row['jump_target'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return $row;
        }, $rows);

        HallFeature::query()->upsert(
            $rows,
            ['feature_id'],
            ['feature_name', 'feature_type', 'unlock_condition', 'jump_target', 'updated_at'],
        );
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('feature_id', 'like', "%{$search}%")
                    ->orWhere('feature_name', 'like', "%{$search}%")
                    ->orWhere('feature_type', 'like', "%{$search}%")
                    ->orWhere('jump_target->page', 'like', "%{$search}%");

                if (is_numeric($search)) {
                    $builder->orWhere('unlock_condition->level', (int) $search);
                }
            });
        }

        $featureType = $filters['feature_type'] ?? null;

        if (filled($featureType)) {
            $query->where('feature_type', $featureType);
        }

        $unlockLevel = $filters['unlock_level'] ?? null;

        if (filled($unlockLevel) && is_numeric($unlockLevel)) {
            $query->where('unlock_condition->level', (int) $unlockLevel);
        }

        $jumpPage = trim((string) ($filters['jump_page'] ?? ''));

        if ($jumpPage !== '') {
            $query->where('jump_target->page', 'like', "%{$jumpPage}%");
        }
    }
}
