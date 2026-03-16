<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShopBuyRequest;
use App\Models\PlayerProfile;
use App\Services\ShopService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopRuntimeController extends Controller
{
    public function __construct(
        private readonly ShopService $shopService,
    ) {}

    public function commonList(Request $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->shopService->listCommon($playerProfile),
        );
    }

    public function commonBuy(ShopBuyRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->shopService->buyCommon(
                $playerProfile,
                (string) $request->validated('shop_item_id'),
                (int) $request->validated('count', 1),
            ),
        );
    }

    public function sectList(Request $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->shopService->listSect($playerProfile),
        );
    }

    public function sectBuy(ShopBuyRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->shopService->buySect(
                $playerProfile,
                (string) $request->validated('shop_item_id'),
                (int) $request->validated('count', 1),
            ),
        );
    }
}
