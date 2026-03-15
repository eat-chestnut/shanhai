<?php

namespace Database\Seeders;

use App\Services\DungeonContentConfigService;
use Illuminate\Database\Seeder;

class DungeonContentConfigSeeder extends Seeder
{
    public function run(DungeonContentConfigService $service): void
    {
        $service->importFromJson(
            database_path('seeders/data/dungeon_content_config.json'),
        );
    }
}
