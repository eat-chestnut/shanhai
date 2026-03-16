<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RarityConfigsSeeder extends Seeder
{
    public function run(): void
    {
        $rarities = [
            [
                'rarity_key' => 'common',
                'rarity_name' => '普通',
                'sort_order' => 1,
                'text_color' => '#FFFFFF',
                'bg_color' => '#666666',
                'border_color' => '#999999',
                'glow_color' => null,
                'frame_key' => 'frame_common',
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rarity_key' => 'uncommon',
                'rarity_name' => '优秀',
                'sort_order' => 2,
                'text_color' => '#00FF00',
                'bg_color' => '#1a4d1a',
                'border_color' => '#00FF00',
                'glow_color' => '#00FF00',
                'frame_key' => 'frame_uncommon',
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rarity_key' => 'rare',
                'rarity_name' => '稀有',
                'sort_order' => 3,
                'text_color' => '#0070DD',
                'bg_color' => '#003366',
                'border_color' => '#0070DD',
                'glow_color' => '#0070DD',
                'frame_key' => 'frame_rare',
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rarity_key' => 'epic',
                'rarity_name' => '史诗',
                'sort_order' => 4,
                'text_color' => '#A335EE',
                'bg_color' => '#4a1a66',
                'border_color' => '#A335EE',
                'glow_color' => '#A335EE',
                'frame_key' => 'frame_epic',
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rarity_key' => 'legendary',
                'rarity_name' => '传说',
                'sort_order' => 5,
                'text_color' => '#FF8000',
                'bg_color' => '#663300',
                'border_color' => '#FF8000',
                'glow_color' => '#FF8000',
                'frame_key' => 'frame_legendary',
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rarity_key' => 'mythic',
                'rarity_name' => '神话',
                'sort_order' => 6,
                'text_color' => '#FF0000',
                'bg_color' => '#660000',
                'border_color' => '#FF0000',
                'glow_color' => '#FF0000',
                'frame_key' => 'frame_mythic',
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('rarity_configs')->insertOrIgnore($rarities);
    }
}
