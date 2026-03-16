<?php

namespace Database\Seeders;

use App\Models\ShopItemConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ShopItemConfigSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/shop_item_config.json');

        if (! is_file($path)) {
            return;
        }

        /** @var array<string, mixed>|null $payload */
        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload)) {
            return;
        }

        $rows = $payload['shop_item_config'] ?? [];
        $timestamp = Carbon::now();

        DB::transaction(function () use ($rows, $timestamp): void {
            ShopItemConfig::query()->delete();

            foreach ($rows as $index => $row) {
                if (! is_array($row) || (string) ($row['shop_item_id'] ?? '') === '') {
                    continue;
                }

                ShopItemConfig::query()->create([
                    'shop_item_id' => (string) $row['shop_item_id'],
                    'shop_type' => (string) ($row['shop_type'] ?? 'common'),
                    'item_id' => (string) ($row['item_id'] ?? ''),
                    'item_name' => (string) ($row['item_name'] ?? $row['item_id'] ?? ''),
                    'count' => max((int) ($row['count'] ?? 1), 1),
                    'cost_type' => (string) ($row['cost_type'] ?? 'gold'),
                    'cost_value' => max((int) ($row['cost_value'] ?? 0), 0),
                    'buy_limit' => max((int) ($row['buy_limit'] ?? 0), 0),
                    'cycle_type' => (string) ($row['cycle_type'] ?? 'daily'),
                    'sort' => (int) ($row['sort'] ?? $index),
                    'is_open' => (bool) ($row['is_open'] ?? true),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        });
    }
}
