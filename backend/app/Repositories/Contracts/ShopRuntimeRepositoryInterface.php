<?php

namespace App\Repositories\Contracts;

use App\Models\PlayerShopPurchase;
use App\Models\ShopItemConfig;
use Illuminate\Support\Collection;

interface ShopRuntimeRepositoryInterface
{
    public function getOpenItemsByType(string $shopType): Collection;

    public function findShopItem(string $shopType, string $shopItemId): ?ShopItemConfig;

    public function findPlayerPurchase(int $playerId, string $shopItemId, string $cycleKey, bool $forUpdate = false): ?PlayerShopPurchase;

    public function upsertPlayerPurchase(array $attributes, array $values): PlayerShopPurchase;
}
