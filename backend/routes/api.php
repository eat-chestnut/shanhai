<?php

use App\Http\Controllers\Api\CharacterClassController;
use App\Http\Controllers\Api\DungeonContentConfigController;
use App\Http\Controllers\Api\EquipmentConfigController;
use App\Http\Controllers\Api\HallFeatureController;
use App\Http\Controllers\Api\MainlineConfigController;
use App\Http\Controllers\Api\SkillController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::apiResource('character-classes', CharacterClassController::class);
    Route::get('dungeon-content-config', DungeonContentConfigController::class);
    Route::get('equipment-config', EquipmentConfigController::class);
    Route::apiResource('hall-features', HallFeatureController::class);
    Route::get('mainline-config', MainlineConfigController::class);
    Route::apiResource('skills', SkillController::class);
});
