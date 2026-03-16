<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelectClassRequest;
use App\Models\PlayerProfile;
use App\Services\PlayerRuntimeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ClassSelectionController extends Controller
{
    public function __construct(
        private readonly PlayerRuntimeService $playerRuntimeService,
    ) {}

    public function select(SelectClassRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->playerRuntimeService->selectClass($playerProfile, (string) $request->validated('class_id')),
        );
    }
}
