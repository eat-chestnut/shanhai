<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskClaimRequest;
use App\Models\PlayerProfile;
use App\Services\TaskService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskRuntimeController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService,
    ) {}

    public function list(Request $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->taskService->list($playerProfile),
        );
    }

    public function claim(TaskClaimRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->taskService->claim($playerProfile, (string) $request->validated('task_id')),
        );
    }

    public function claimAll(Request $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->taskService->claimAll($playerProfile),
        );
    }
}
