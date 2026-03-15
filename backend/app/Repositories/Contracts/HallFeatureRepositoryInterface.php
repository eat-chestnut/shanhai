<?php

namespace App\Repositories\Contracts;

use App\Models\HallFeature;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface HallFeatureRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function create(array $data): HallFeature;

    public function update(HallFeature $hallFeature, array $data): HallFeature;

    public function delete(HallFeature $hallFeature): bool;

    public function upsertByFeatureId(array $rows): void;
}
