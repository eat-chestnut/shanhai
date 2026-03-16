<?php

namespace Database\Seeders;

use App\Models\RarityConfig;
use Illuminate\Database\Seeder;

class RarityConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = RarityConfig::getDefaultConfigs();
        
        foreach ($configs as $config) {
            RarityConfig::updateOrCreate(
                ['rarity_key' => $config['rarity_key']],
                $config
            );
        }
    }
}
