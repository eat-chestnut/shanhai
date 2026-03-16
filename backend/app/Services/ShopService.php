<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\PlayerProfile;
use App\Models\ShopItemConfig;
use App\Repositories\Contracts\PlayerRuntimeRepositoryInterface;
use App\Repositories\Contracts\ShopRuntimeRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ShopService
{
    public function __construct(
        private readonly ShopRuntimeRepositoryInterface $shopRuntimeRepository,
        private readonly PlayerRuntimeRepositoryInterface $playerRuntimeRepository,
        private readonly InventoryService $inventoryService,
        private readonly PlayerRuntimeService $playerRuntimeService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function listCommon(PlayerProfile $playerProfile): array
    {
        return $this->buildShopPayload($playerProfile, 'common');
    }

    /**
     * @return array<string, mixed>
     */
    public function buyCommon(PlayerProfile $playerProfile, string $shopItemId, int $count): array
    {
        return $this->buy($playerProfile, 'common', $shopItemId, $count);
    }

    /**
     * @return array<string, mixed>
     */
    public function listSect(PlayerProfile $playerProfile): array
    {
        return $this->buildShopPayload($playerProfile, 'sect');
    }

    /**
     * @return array<string, mixed>
     */
    public function buySect(PlayerProfile $playerProfile, string $shopItemId, int $count): array
    {
        return $this->buy($playerProfile, 'sect', $shopItemId, $count);
    }

    /**
     * @return array<string, mixed>
     */
    private function buy(PlayerProfile $playerProfile, string $shopType, string $shopItemId, int $count): array
    {
        return DB::transaction(function () use ($playerProfile, $shopType, $shopItemId, $count): array {
            $purchaseCount = max($count, 1);
            $lockedProfile = $this->playerRuntimeRepository->getProfileForUpdate((int) $playerProfile->player_id);

            if (! $lockedProfile) {
                throw new ApiException('玩家不存在', 40442, 404);
            }

            $shopItem = $this->shopRuntimeRepository->findShopItem($shopType, $shopItemId);

            if (! $shopItem) {
                throw new ApiException('商品不存在', 40481, 404);
            }

            $cycleKey = $this->resolveCycleKey($shopItem);
            $purchase = $this->shopRuntimeRepository->findPlayerPurchase(
                (int) $lockedProfile->player_id,
                $shopItem->shop_item_id,
                $cycleKey,
                true,
            );
            $boughtCount = (int) ($purchase?->bought_count ?? 0);

            if ((int) $shopItem->buy_limit > 0 && $boughtCount + $purchaseCount > (int) $shopItem->buy_limit) {
                throw new ApiException('商品已售罄', 40081, 400);
            }

            $lockedProfile = $this->inventoryService->spendCurrency(
                $lockedProfile,
                (string) $shopItem->cost_type,
                (int) $shopItem->cost_value * $purchaseCount,
                '货币不足',
            );

            $rewardResult = $this->inventoryService->applyRewards($lockedProfile, [[
                'item_id' => (string) $shopItem->item_id,
                'count' => (int) $shopItem->count * $purchaseCount,
            ]]);
            $updatedProfile = $this->playerRuntimeService->syncComputedFields($rewardResult['player_profile']);

            $this->shopRuntimeRepository->upsertPlayerPurchase(
                [
                    'player_id' => (int) $updatedProfile->player_id,
                    'shop_item_id' => $shopItem->shop_item_id,
                    'cycle_key' => $cycleKey,
                ],
                [
                    'bought_count' => $boughtCount + $purchaseCount,
                ],
            );

            return [
                'shop_type' => $shopType,
                'items' => $this->buildShopPayload($updatedProfile, $shopType)['items'],
                'inventory' => $this->playerRuntimeService->buildInventoryPayload((int) $updatedProfile->player_id),
                'player' => $this->playerRuntimeService->getInitPayload($updatedProfile)['player'],
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function buildShopPayload(PlayerProfile $playerProfile, string $shopType): array
    {
        $items = $this->shopRuntimeRepository->getOpenItemsByType($shopType)
            ->map(function (ShopItemConfig $shopItem) use ($playerProfile): array {
                $cycleKey = $this->resolveCycleKey($shopItem);
                $purchase = $this->shopRuntimeRepository->findPlayerPurchase(
                    (int) $playerProfile->player_id,
                    $shopItem->shop_item_id,
                    $cycleKey,
                );
                $boughtCount = (int) ($purchase?->bought_count ?? 0);
                $buyLimit = (int) $shopItem->buy_limit;

                return [
                    'shop_item_id' => $shopItem->shop_item_id,
                    'item_id' => $shopItem->item_id,
                    'item_name' => $shopItem->item_name,
                    'count' => (int) $shopItem->count,
                    'cost_type' => $shopItem->cost_type,
                    'cost_value' => (int) $shopItem->cost_value,
                    'buy_limit' => $buyLimit,
                    'bought_count' => $boughtCount,
                    'is_sold_out' => $buyLimit > 0 && $boughtCount >= $buyLimit,
                ];
            })
            ->values()
            ->all();

        return [
            'shop_type' => $shopType,
            'items' => $items,
            'currencies' => [
                'gold' => (int) $playerProfile->gold,
                'jade' => (int) $playerProfile->jade,
                'contribution' => (int) $playerProfile->contribution,
            ],
        ];
    }

    private function resolveCycleKey(ShopItemConfig $shopItem): string
    {
        return $shopItem->cycle_type === 'daily'
            ? Carbon::now()->toDateString()
            : 'permanent';
    }
}
