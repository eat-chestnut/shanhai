<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\PlayerProfile;
use App\Repositories\Contracts\PlayerRuntimeRepositoryInterface;

class InventoryService
{
    public function __construct(
        private readonly PlayerRuntimeRepositoryInterface $playerRuntimeRepository,
    ) {}

    public function getItemCount(int $playerId, string $itemId): int
    {
        return (int) ($this->playerRuntimeRepository->findInventoryItem($playerId, $itemId)?->count ?? 0);
    }

    public function consumeItem(int $playerId, string $itemId, int $count, string $errorMessage = '材料不足'): void
    {
        if ($count <= 0) {
            return;
        }

        if ($this->getItemCount($playerId, $itemId) < $count) {
            throw new ApiException($errorMessage, 40061, 400);
        }

        $this->playerRuntimeRepository->incrementItemCount($playerId, $itemId, -$count);
    }

    public function grantItem(int $playerId, string $itemId, int $count): void
    {
        if ($itemId === '' || $count <= 0) {
            return;
        }

        $this->playerRuntimeRepository->incrementItemCount($playerId, $itemId, $count);
    }

    public function spendCurrency(PlayerProfile $playerProfile, string $currencyType, int $amount, string $errorMessage = '货币不足'): PlayerProfile
    {
        if ($amount <= 0) {
            return $playerProfile;
        }

        $current = match ($currencyType) {
            'gold' => (int) $playerProfile->gold,
            'jade' => (int) $playerProfile->jade,
            'contribution' => (int) $playerProfile->contribution,
            default => throw new ApiException('参数错误', 42201, 422),
        };

        if ($current < $amount) {
            throw new ApiException($errorMessage, 40091, 400);
        }

        $playerProfile->forceFill([
            $currencyType => $current - $amount,
        ])->save();

        return $playerProfile->refresh();
    }

    /**
     * @param  list<array<string, mixed>>  $rewards
     * @return array<string, mixed>
     */
    public function applyRewards(PlayerProfile $playerProfile, array $rewards): array
    {
        $currencyDelta = [
            'gold' => 0,
            'jade' => 0,
            'contribution' => 0,
        ];
        $itemRewards = [];

        foreach ($rewards as $reward) {
            $itemId = (string) ($reward['item_id'] ?? '');
            $count = max((int) ($reward['count'] ?? 0), 0);

            if ($itemId === '' || $count <= 0) {
                continue;
            }

            if (array_key_exists($itemId, $currencyDelta)) {
                $currencyDelta[$itemId] += $count;

                continue;
            }

            $this->grantItem((int) $playerProfile->player_id, $itemId, $count);
            $itemRewards[] = [
                'item_id' => $itemId,
                'count' => $count,
            ];
        }

        $playerProfile->forceFill([
            'gold' => (int) $playerProfile->gold + $currencyDelta['gold'],
            'jade' => (int) $playerProfile->jade + $currencyDelta['jade'],
            'contribution' => (int) $playerProfile->contribution + $currencyDelta['contribution'],
        ])->save();

        return [
            'player_profile' => $playerProfile->refresh(),
            'item_rewards' => $itemRewards,
            'currency_delta' => $currencyDelta,
        ];
    }
}
