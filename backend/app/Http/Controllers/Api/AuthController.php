<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\PlayerRuntimeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly PlayerRuntimeService $playerRuntimeService,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->playerRuntimeService->login($request->validated()),
        );
    }
}
