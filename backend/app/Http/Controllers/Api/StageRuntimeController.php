<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StageDifficultyListRequest;
use App\Http\Requests\StageNodeDetailRequest;
use App\Models\PlayerProfile;
use App\Services\StageRuntimeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StageRuntimeController extends Controller
{
    public function __construct(
        private readonly StageRuntimeService $stageRuntimeService,
    ) {}

    public function chapterList(Request $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->stageRuntimeService->listChapters($playerProfile),
        );
    }

    public function nodeDetail(StageNodeDetailRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->stageRuntimeService->getNodeDetail($playerProfile, (string) $request->validated('node_id')),
        );
    }

    public function difficultyList(StageDifficultyListRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->stageRuntimeService->listDifficulties($playerProfile, (string) $request->validated('node_id')),
        );
    }
}
