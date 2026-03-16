<?php

namespace App\Repositories;

use App\Models\BlueAffix;
use App\Models\Equipment;
use App\Models\EquipmentSet;
use App\Models\Gem;
use App\Models\PurpleRefinement;
use App\Repositories\Contracts\EquipmentConfigRepositoryInterface;
use Illuminate\Support\Collection;

class EquipmentConfigRepository implements EquipmentConfigRepositoryInterface
{
    public function truncateAll(): void
    {
        PurpleRefinement::query()->delete();
        BlueAffix::query()->delete();
        Gem::query()->delete();
        EquipmentSet::query()->delete();
        Equipment::query()->delete();
    }

    public function insertEquipment(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        Equipment::query()->insert($rows);
    }

    public function insertSets(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        EquipmentSet::query()->insert($rows);
    }

    public function insertGems(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        Gem::query()->insert($rows);
    }

    public function insertBlueAffixes(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        BlueAffix::query()->insert($rows);
    }

    public function insertPurpleRefinements(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        PurpleRefinement::query()->insert($rows);
    }

    public function getOrderedEquipment(): Collection
    {
        return Equipment::query()
            ->orderBy('equip_id')
            ->get();
    }

    public function getOrderedSets(): Collection
    {
        return EquipmentSet::query()
            ->orderBy('set_id')
            ->get();
    }

    public function getOrderedGems(): Collection
    {
        return Gem::query()
            ->orderBy('gem_id')
            ->get();
    }

    public function getOrderedBlueAffixes(): Collection
    {
        return BlueAffix::query()
            ->orderBy('affix_id')
            ->get();
    }

    public function getOrderedPurpleRefinements(): Collection
    {
        return PurpleRefinement::query()
            ->orderBy('refinement_id')
            ->get();
    }
}
