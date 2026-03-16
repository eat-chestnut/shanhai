<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DungeonDetailRequest;
use App\Models\PlayerProfile;
use App\Services\DungeonRuntimeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DungeonRuntimeController extends Controller
{
    public function __construct(
        private readonly DungeonRuntimeService $dungeonRuntimeService,
    ) {}

    public function list(Request $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->dungeonRuntimeService->listDungeons($playerProfile),
        );
    }

    public function detail(DungeonDetailRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->dungeonRuntimeService->getDungeonDetail($playerProfile, (string) $request->validated('dungeon_id')),
        );
    }
}
