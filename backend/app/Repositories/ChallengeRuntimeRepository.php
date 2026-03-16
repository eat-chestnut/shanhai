<?php

namespace App\Repositories;

use App\Models\ChallengeConfig;
use App\Models\PlayerChallengeProgress;
use App\Repositories\Contracts\ChallengeRuntimeRepositoryInterface;
use Illuminate\Support\Collection;

class ChallengeRuntimeRepository implements ChallengeRuntimeRepositoryInterface
{
    public function getOpenChallenges(): Collection
    {
        return ChallengeConfig::query()
            ->where('is_open', true)
            ->orderBy('sort')
            ->orderBy('challenge_id')
            ->get();
    }

    public function findChallenge(string $challengeId): ?ChallengeConfig
    {
        return ChallengeConfig::query()
            ->where('challenge_id', $challengeId)
            ->first();
    }

    public function findPlayerProgress(int $playerId, string $challengeId, bool $forUpdate = false): ?PlayerChallengeProgress
    {
        $query = PlayerChallengeProgress::query()
            ->where('player_id', $playerId)
            ->where('challenge_id', $challengeId);

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function upsertPlayerProgress(array $attributes, array $values): PlayerChallengeProgress
    {
        return PlayerChallengeProgress::query()->updateOrCreate($attributes, $values);
    }
}
