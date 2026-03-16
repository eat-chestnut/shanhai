<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BattlePrepareRequest;
use App\Http\Requests\BattleSettleRequest;
use App\Models\PlayerProfile;
use App\Services\BattleRuntimeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class BattleRuntimeController extends Controller
{
    public function __construct(
        private readonly BattleRuntimeService $battleRuntimeService,
    ) {}

    public function prepare(BattlePrepareRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->battleRuntimeService->prepare($playerProfile, $request->validated()),
        );
    }

    public function settle(BattleSettleRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->battleRuntimeService->settle($playerProfile, $request->validated()),
        );
    }
}
