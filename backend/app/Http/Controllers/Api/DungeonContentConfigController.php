<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DungeonContentConfigService;
use Illuminate\Http\JsonResponse;

class DungeonContentConfigController extends Controller
{
    public function __invoke(DungeonContentConfigService $service): JsonResponse
    {
        return response()->json($service->exportToArray());
    }
}
