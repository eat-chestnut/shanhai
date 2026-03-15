<?php

namespace Database\Seeders;

use App\Services\EquipmentConfigService;
use Illuminate\Database\Seeder;

class EquipmentConfigSeeder extends Seeder
{
    public function run(EquipmentConfigService $service): void
    {
        $service->importFromJson(
            database_path('seeders/data/equipment_config.json'),
        );
    }
}
