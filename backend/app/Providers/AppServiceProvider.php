<?php

namespace App\Providers;

use App\Repositories\CharacterClassRepository;
use App\Repositories\Contracts\CharacterClassRepositoryInterface;
use App\Repositories\Contracts\HallFeatureRepositoryInterface;
use App\Repositories\HallFeatureRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CharacterClassRepositoryInterface::class, CharacterClassRepository::class);
        $this->app->bind(HallFeatureRepositoryInterface::class, HallFeatureRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
