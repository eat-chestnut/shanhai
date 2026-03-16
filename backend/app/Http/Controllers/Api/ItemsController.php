<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $items = Item::query()
            ->where('is_enabled', true)
            ->orderBy('item_type')
            ->orderBy('rarity')
            ->orderBy('item_id')
            ->get()
            ->map(function (Item $item): array {
                return [
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'item_type' => $item->item_type,
                    'rarity' => $item->rarity,
                    'icon' => $item->icon,
                    'desc' => $item->desc,
                    'is_enabled' => $item->is_enabled,
                ];
            })
            ->all();

        return ApiResponse::success([
            'items' => $items,
        ]);
    }
}
