<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BattleRuntimeController;
use App\Http\Controllers\Api\CharacterClassController;
use App\Http\Controllers\Api\ClassSelectionController;
use App\Http\Controllers\Api\DungeonContentConfigController;
use App\Http\Controllers\Api\DungeonRuntimeController;
use App\Http\Controllers\Api\EquipmentConfigController;
use App\Http\Controllers\Api\HallFeatureController;
use App\Http\Controllers\Api\InventoryRuntimeController;
use App\Http\Controllers\Api\MainlineConfigController;
use App\Http\Controllers\Api\PlayerRuntimeController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\StageRuntimeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::apiResource('character-classes', CharacterClassController::class);
    Route::get('dungeon-content-config', DungeonContentConfigController::class);
    Route::get('equipment-config', EquipmentConfigController::class);
    Route::apiResource('hall-features', HallFeatureController::class);
    Route::get('mainline-config', MainlineConfigController::class);
    Route::apiResource('skills', SkillController::class);

    Route::middleware('player.auth')->group(function (): void {
        Route::get('player/init', [PlayerRuntimeController::class, 'init']);
        Route::post('class/select', [ClassSelectionController::class, 'select']);
        Route::get('stage/chapter/list', [StageRuntimeController::class, 'chapterList']);
        Route::get('stage/node/detail', [StageRuntimeController::class, 'nodeDetail']);
        Route::get('stage/difficulty/list', [StageRuntimeController::class, 'difficultyList']);
        Route::get('dungeon/list', [DungeonRuntimeController::class, 'list']);
        Route::get('dungeon/detail', [DungeonRuntimeController::class, 'detail']);
        Route::get('inventory/list', [InventoryRuntimeController::class, 'index']);
        Route::post('battle/prepare', [BattleRuntimeController::class, 'prepare']);
        Route::post('battle/settle', [BattleRuntimeController::class, 'settle']);
    });
});
