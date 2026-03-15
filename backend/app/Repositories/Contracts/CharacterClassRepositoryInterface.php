<?php

namespace App\Repositories\Contracts;

use App\Models\CharacterClass;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CharacterClassRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function create(array $data): CharacterClass;

    public function update(CharacterClass $characterClass, array $data): CharacterClass;

    public function delete(CharacterClass $characterClass): bool;

    public function upsertByClassId(array $rows): void;
}
