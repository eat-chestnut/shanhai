<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface EquipmentConfigRepositoryInterface
{
    public function truncateAll(): void;

    public function insertEquipment(array $rows): void;

    public function insertSets(array $rows): void;

    public function insertGems(array $rows): void;

    public function insertBlueAffixes(array $rows): void;

    public function insertPurpleRefinements(array $rows): void;

    public function getOrderedEquipment(): Collection;

    public function getOrderedSets(): Collection;

    public function getOrderedGems(): Collection;

    public function getOrderedBlueAffixes(): Collection;

    public function getOrderedPurpleRefinements(): Collection;
}
