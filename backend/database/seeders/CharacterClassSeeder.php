<?php

namespace Database\Seeders;

use App\Services\CharacterClassService;
use Illuminate\Database\Seeder;

class CharacterClassSeeder extends Seeder
{
    public function run(CharacterClassService $service): void
    {
        $service->syncFromJson(
            database_path('seeders/data/class_config.json'),
        );
    }
}
