<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RarityConfig;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RarityConfigsController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $configs = RarityConfig::query()
            ->where('is_enabled', true)
            ->orderBy('sort')
            ->orderBy('rarity_key')
            ->get()
            ->map(function (RarityConfig $config): array {
                return [
                    'rarity_key' => $config->rarity_key,
                    'rarity_name' => $config->rarity_name,
                    'sort' => $config->sort,
                    'text_color' => $config->text_color,
                    'bg_color' => $config->bg_color,
                    'border_color' => $config->border_color,
                    'frame_key' => $config->frame_key,
                    'is_enabled' => $config->is_enabled,
                ];
            })
            ->all();

        return ApiResponse::success([
            'rarity_configs' => $configs,
        ]);
    }
}
