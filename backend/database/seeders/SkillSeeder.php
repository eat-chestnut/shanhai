<?php

namespace Database\Seeders;

use App\Services\SkillService;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(SkillService $service): void
    {
        $service->syncFromJson(
            database_path('seeders/data/skill_config.json'),
        );
    }
}
