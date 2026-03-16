<?php

namespace App\Repositories\Contracts;

use App\Models\Skill;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SkillRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function create(array $data): Skill;

    public function update(Skill $skill, array $data): Skill;

    public function delete(Skill $skill): bool;

    public function upsertBySkillId(array $rows): void;
}
