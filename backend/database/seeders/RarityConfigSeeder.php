<?php

use Illuminate\Database\Seeder;

class RarityConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = App\Models\RarityConfig::getDefaultConfigs();
        
        foreach ($configs as $config) {
            App\Models\RarityConfig::updateOrCreate(
                ['rarity_key' => $config['rarity_key']],
                $config
            );
        }
    }
}
