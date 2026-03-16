<?php

namespace App\Providers;

use App\Repositories\CharacterClassRepository;
use App\Repositories\Contracts\CharacterClassRepositoryInterface;
use App\Repositories\Contracts\DungeonContentConfigRepositoryInterface;
use App\Repositories\Contracts\EquipmentConfigRepositoryInterface;
use App\Repositories\Contracts\HallFeatureRepositoryInterface;
use App\Repositories\Contracts\MainlineConfigRepositoryInterface;
use App\Repositories\Contracts\SkillRepositoryInterface;
use App\Repositories\DungeonContentConfigRepository;
use App\Repositories\EquipmentConfigRepository;
use App\Repositories\HallFeatureRepository;
use App\Repositories\MainlineConfigRepository;
use App\Repositories\SkillRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CharacterClassRepositoryInterface::class, CharacterClassRepository::class);
        $this->app->bind(DungeonContentConfigRepositoryInterface::class, DungeonContentConfigRepository::class);
        $this->app->bind(EquipmentConfigRepositoryInterface::class, EquipmentConfigRepository::class);
        $this->app->bind(HallFeatureRepositoryInterface::class, HallFeatureRepository::class);
        $this->app->bind(MainlineConfigRepositoryInterface::class, MainlineConfigRepository::class);
        $this->app->bind(SkillRepositoryInterface::class, SkillRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
