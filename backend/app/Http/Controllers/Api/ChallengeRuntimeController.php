<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChallengeDetailRequest;
use App\Models\PlayerProfile;
use App\Services\ChallengeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeRuntimeController extends Controller
{
    public function __construct(
        private readonly ChallengeService $challengeService,
    ) {}

    public function list(Request $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->challengeService->list($playerProfile),
        );
    }

    public function detail(ChallengeDetailRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->challengeService->detail($playerProfile, (string) $request->validated('challenge_id')),
        );
    }
}
