<?php

namespace Database\Seeders;

use App\Services\HallFeatureService;
use Illuminate\Database\Seeder;

class HallFeatureSeeder extends Seeder
{
    public function run(HallFeatureService $service): void
    {
        $service->syncFromJson(
            database_path('seeders/data/hall_feature_config.json'),
        );
    }
}
