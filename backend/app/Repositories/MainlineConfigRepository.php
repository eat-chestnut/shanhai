<?php

namespace App\Repositories;

use App\Models\MainlineChapter;
use App\Models\MainlineDifficulty;
use App\Models\MainlineNode;
use App\Repositories\Contracts\MainlineConfigRepositoryInterface;
use Illuminate\Support\Collection;

class MainlineConfigRepository implements MainlineConfigRepositoryInterface
{
    public function truncateAll(): void
    {
        MainlineDifficulty::query()->delete();
        MainlineNode::query()->delete();
        MainlineChapter::query()->delete();
    }

    public function insertChapters(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        MainlineChapter::query()->insert($rows);
    }

    public function insertNodes(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        MainlineNode::query()->insert($rows);
    }

    public function insertDifficulties(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        MainlineDifficulty::query()->insert($rows);
    }

    public function syncDifficultyIds(): void
    {
        MainlineNode::syncAllDifficultyIds();
    }

    public function getOrderedChapters(): Collection
    {
        return MainlineChapter::query()
            ->orderBy('chapter_id')
            ->get();
    }

    public function getOrderedNodes(): Collection
    {
        return MainlineNode::query()
            ->orderBy('chapter_id')
            ->orderBy('node_id')
            ->get();
    }

    public function getAllNodes(): Collection
    {
        return MainlineNode::query()->get();
    }

    public function getAllDifficulties(): Collection
    {
        return MainlineDifficulty::query()->get();
    }
}
