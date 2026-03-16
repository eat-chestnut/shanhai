<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            RarityConfigSeeder::class,
            ItemsSeeder::class,
            CharacterClassSeeder::class,
            SkillSeeder::class,
            HallFeatureSeeder::class,
            MainlineConfigSeeder::class,
            DungeonContentConfigSeeder::class,
            EquipmentConfigSeeder::class,
            TaskConfigSeeder::class,
            ShopItemConfigSeeder::class,
            IdleRewardRuleSeeder::class,
            ChallengeConfigSeeder::class,
        ]);
    }
}
