<?php

namespace App\Providers;

use App\Repositories\BattleRuntimeRepository;
use App\Repositories\CharacterClassRepository;
use App\Repositories\Contracts\BattleRuntimeRepositoryInterface;
use App\Repositories\Contracts\CharacterClassRepositoryInterface;
use App\Repositories\Contracts\DungeonContentConfigRepositoryInterface;
use App\Repositories\Contracts\EquipmentConfigRepositoryInterface;
use App\Repositories\Contracts\EquipmentRuntimeRepositoryInterface;
use App\Repositories\Contracts\HallFeatureRepositoryInterface;
use App\Repositories\Contracts\MainlineConfigRepositoryInterface;
use App\Repositories\Contracts\PlayerRuntimeRepositoryInterface;
use App\Repositories\Contracts\ShopRuntimeRepositoryInterface;
use App\Repositories\Contracts\SkillRepositoryInterface;
use App\Repositories\Contracts\TaskRuntimeRepositoryInterface;
use App\Repositories\DungeonContentConfigRepository;
use App\Repositories\EquipmentConfigRepository;
use App\Repositories\EquipmentRuntimeRepository;
use App\Repositories\HallFeatureRepository;
use App\Repositories\MainlineConfigRepository;
use App\Repositories\PlayerRuntimeRepository;
use App\Repositories\ShopRuntimeRepository;
use App\Repositories\SkillRepository;
use App\Repositories\TaskRuntimeRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BattleRuntimeRepositoryInterface::class, BattleRuntimeRepository::class);
        $this->app->bind(CharacterClassRepositoryInterface::class, CharacterClassRepository::class);
        $this->app->bind(DungeonContentConfigRepositoryInterface::class, DungeonContentConfigRepository::class);
        $this->app->bind(EquipmentRuntimeRepositoryInterface::class, EquipmentRuntimeRepository::class);
        $this->app->bind(EquipmentConfigRepositoryInterface::class, EquipmentConfigRepository::class);
        $this->app->bind(HallFeatureRepositoryInterface::class, HallFeatureRepository::class);
        $this->app->bind(MainlineConfigRepositoryInterface::class, MainlineConfigRepository::class);
        $this->app->bind(PlayerRuntimeRepositoryInterface::class, PlayerRuntimeRepository::class);
        $this->app->bind(ShopRuntimeRepositoryInterface::class, ShopRuntimeRepository::class);
        $this->app->bind(SkillRepositoryInterface::class, SkillRepository::class);
        $this->app->bind(TaskRuntimeRepositoryInterface::class, TaskRuntimeRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
