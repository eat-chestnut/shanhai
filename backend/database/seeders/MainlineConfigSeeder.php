<?php

namespace Database\Seeders;

use App\Services\MainlineConfigService;
use Illuminate\Database\Seeder;

class MainlineConfigSeeder extends Seeder
{
    public function run(MainlineConfigService $service): void
    {
        $service->importFromJson(
            database_path('seeders/data/mainline_config.json'),
        );
    }
}
