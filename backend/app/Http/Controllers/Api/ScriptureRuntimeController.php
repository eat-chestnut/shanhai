<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScriptureDetailRequest;
use App\Http\Requests\ScriptureUpgradeRequest;
use App\Models\PlayerProfile;
use App\Services\ScriptureRuntimeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScriptureRuntimeController extends Controller
{
    public function __construct(
        private readonly ScriptureRuntimeService $scriptureRuntimeService,
    ) {}

    public function list(Request $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->scriptureRuntimeService->listScriptures($playerProfile),
        );
    }

    public function detail(ScriptureDetailRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->scriptureRuntimeService->getScriptureDetail($playerProfile, (string) $request->validated('scripture_id')),
        );
    }

    public function upgrade(ScriptureUpgradeRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->scriptureRuntimeService->upgradeScripture(
                $playerProfile,
                (string) $request->validated('scripture_id'),
                (int) $request->validated('target_world_level'),
            ),
        );
    }
}
