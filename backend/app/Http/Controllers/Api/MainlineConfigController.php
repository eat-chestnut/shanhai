<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MainlineConfigService;
use Illuminate\Http\JsonResponse;

class MainlineConfigController extends Controller
{
    public function __invoke(MainlineConfigService $service): JsonResponse
    {
        return response()->json($service->exportToArray());
    }
}
