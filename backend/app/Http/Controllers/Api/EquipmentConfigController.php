<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EquipmentConfigService;
use Illuminate\Http\JsonResponse;

class EquipmentConfigController extends Controller
{
    public function __invoke(EquipmentConfigService $service): JsonResponse
    {
        return response()->json($service->exportToArray());
    }
}
