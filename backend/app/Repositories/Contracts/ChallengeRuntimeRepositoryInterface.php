<?php

namespace App\Repositories\Contracts;

use App\Models\ChallengeConfig;
use App\Models\PlayerChallengeProgress;
use Illuminate\Support\Collection;

interface ChallengeRuntimeRepositoryInterface
{
    public function getOpenChallenges(): Collection;

    public function findChallenge(string $challengeId): ?ChallengeConfig;

    public function findPlayerProgress(int $playerId, string $challengeId, bool $forUpdate = false): ?PlayerChallengeProgress;

    public function upsertPlayerProgress(array $attributes, array $values): PlayerChallengeProgress;
}
