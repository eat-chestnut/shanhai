<?php

namespace App\Repositories;

use App\Models\PlayerShopPurchase;
use App\Models\ShopItemConfig;
use App\Repositories\Contracts\ShopRuntimeRepositoryInterface;
use Illuminate\Support\Collection;

class ShopRuntimeRepository implements ShopRuntimeRepositoryInterface
{
    public function getOpenItemsByType(string $shopType): Collection
    {
        return ShopItemConfig::query()
            ->where('shop_type', $shopType)
            ->where('is_open', true)
            ->orderBy('sort')
            ->orderBy('shop_item_id')
            ->get();
    }

    public function findShopItem(string $shopType, string $shopItemId): ?ShopItemConfig
    {
        return ShopItemConfig::query()
            ->where('shop_type', $shopType)
            ->where('shop_item_id', $shopItemId)
            ->where('is_open', true)
            ->first();
    }

    public function findPlayerPurchase(int $playerId, string $shopItemId, string $cycleKey, bool $forUpdate = false): ?PlayerShopPurchase
    {
        $query = PlayerShopPurchase::query()
            ->where('player_id', $playerId)
            ->where('shop_item_id', $shopItemId)
            ->where('cycle_key', $cycleKey);

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function upsertPlayerPurchase(array $attributes, array $values): PlayerShopPurchase
    {
        return PlayerShopPurchase::query()->updateOrCreate($attributes, $values);
    }
}
