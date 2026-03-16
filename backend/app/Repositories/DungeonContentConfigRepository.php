<?php

namespace App\Repositories;

use App\Models\Dungeon;
use App\Models\DungeonDifficulty;
use App\Models\Monster;
use App\Models\MonsterDrop;
use App\Repositories\Contracts\DungeonContentConfigRepositoryInterface;
use Illuminate\Support\Collection;

class DungeonContentConfigRepository implements DungeonContentConfigRepositoryInterface
{
    public function truncateAll(): void
    {
        MonsterDrop::query()->delete();
        DungeonDifficulty::query()->delete();
        Monster::query()->delete();
        Dungeon::query()->delete();
    }

    public function insertDungeons(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        Dungeon::query()->insert($rows);
    }

    public function insertDifficulties(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        DungeonDifficulty::query()->insert($rows);
    }

    public function insertMonsters(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        Monster::query()->insert($rows);
    }

    public function insertDrops(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        MonsterDrop::query()->insert($rows);
    }

    public function getOrderedDungeons(): Collection
    {
        return Dungeon::query()
            ->orderBy('dungeon_id')
            ->get();
    }

    public function getOrderedDifficulties(): Collection
    {
        return DungeonDifficulty::query()
            ->orderBy('dungeon_id')
            ->orderByRaw("CASE difficulty_id WHEN 'easy' THEN 0 WHEN 'normal' THEN 1 WHEN 'hard' THEN 2 WHEN 'nightmare' THEN 3 WHEN 'epic' THEN 4 ELSE 99 END")
            ->orderBy('difficulty_id')
            ->get();
    }

    public function getOrderedMonsters(): Collection
    {
        return Monster::query()
            ->orderBy('monster_id')
            ->get();
    }

    public function getOrderedDrops(): Collection
    {
        return MonsterDrop::query()
            ->orderBy('monster_id')
            ->orderByRaw("CASE drop_kind WHEN 'boss_fixed' THEN 0 WHEN 'boss_core' THEN 1 ELSE 2 END")
            ->orderBy('item_id')
            ->get();
    }
}
