<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface MainlineConfigRepositoryInterface
{
    public function truncateAll(): void;

    public function insertChapters(array $rows): void;

    public function insertNodes(array $rows): void;

    public function insertDifficulties(array $rows): void;

    public function syncDifficultyIds(): void;

    public function getOrderedChapters(): Collection;

    public function getOrderedNodes(): Collection;

    public function getAllNodes(): Collection;

    public function getAllDifficulties(): Collection;
}
