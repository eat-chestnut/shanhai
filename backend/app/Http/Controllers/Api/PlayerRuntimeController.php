<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlayerProfile;
use App\Services\PlayerRuntimeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerRuntimeController extends Controller
{
    public function __construct(
        private readonly PlayerRuntimeService $playerRuntimeService,
    ) {}

    public function init(Request $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->playerRuntimeService->getInitPayload($playerProfile),
        );
    }
}
