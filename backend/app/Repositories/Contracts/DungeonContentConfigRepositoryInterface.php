<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface DungeonContentConfigRepositoryInterface
{
    public function truncateAll(): void;

    public function insertDungeons(array $rows): void;

    public function insertDifficulties(array $rows): void;

    public function insertMonsters(array $rows): void;

    public function insertDrops(array $rows): void;

    public function getOrderedDungeons(): Collection;

    public function getOrderedDifficulties(): Collection;

    public function getOrderedMonsters(): Collection;

    public function getOrderedDrops(): Collection;
}
