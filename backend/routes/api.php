<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BattleRuntimeController;
use App\Http\Controllers\Api\ChallengeRuntimeController;
use App\Http\Controllers\Api\CharacterClassController;
use App\Http\Controllers\Api\ClassSelectionController;
use App\Http\Controllers\Api\DungeonContentConfigController;
use App\Http\Controllers\Api\DungeonRuntimeController;
use App\Http\Controllers\Api\EquipmentConfigController;
use App\Http\Controllers\Api\EquipmentRuntimeController;
use App\Http\Controllers\Api\HallFeatureController;
use App\Http\Controllers\Api\IdleRuntimeController;
use App\Http\Controllers\Api\InventoryRuntimeController;
use App\Http\Controllers\Api\ItemsController;
use App\Http\Controllers\Api\MainlineConfigController;
use App\Http\Controllers\Api\PlayerRuntimeController;
use App\Http\Controllers\Api\RarityConfigsController;
use App\Http\Controllers\Api\ShopRuntimeController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\StageRuntimeController;
use App\Http\Controllers\Api\TaskRuntimeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::apiResource('character-classes', CharacterClassController::class);
    Route::get('dungeon-content-config', DungeonContentConfigController::class);
    Route::get('equipment-config', EquipmentConfigController::class);
    Route::apiResource('hall-features', HallFeatureController::class);
    Route::get('mainline-config', MainlineConfigController::class);
    Route::get('items/list', [ItemsController::class, 'list']);
    Route::get('rarity-configs/list', [RarityConfigsController::class, 'list']);
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
        Route::get('equipment/detail', [EquipmentRuntimeController::class, 'detail']);
        Route::post('equipment/equip', [EquipmentRuntimeController::class, 'equip']);
        Route::post('equipment/unequip', [EquipmentRuntimeController::class, 'unequip']);
        Route::post('equipment/star_up', [EquipmentRuntimeController::class, 'starUp']);
        Route::post('equipment/socket_gem', [EquipmentRuntimeController::class, 'socketGem']);
        Route::post('equipment/extract_blue_affix', [EquipmentRuntimeController::class, 'extractBlueAffix']);
        Route::post('equipment/refine_purple_affix', [EquipmentRuntimeController::class, 'refinePurpleAffix']);
        Route::get('task/list', [TaskRuntimeController::class, 'list']);
        Route::post('task/claim', [TaskRuntimeController::class, 'claim']);
        Route::post('task/claim_all', [TaskRuntimeController::class, 'claimAll']);
        Route::get('idle/status', [IdleRuntimeController::class, 'status']);
        Route::post('idle/claim', [IdleRuntimeController::class, 'claim']);
        Route::get('idle/rules', [IdleRuntimeController::class, 'rules']);
        Route::get('challenge/list', [ChallengeRuntimeController::class, 'list']);
        Route::get('challenge/detail', [ChallengeRuntimeController::class, 'detail']);
        Route::get('shop/common/list', [ShopRuntimeController::class, 'commonList']);
        Route::post('shop/common/buy', [ShopRuntimeController::class, 'commonBuy']);
        Route::get('shop/sect/list', [ShopRuntimeController::class, 'sectList']);
        Route::post('shop/sect/buy', [ShopRuntimeController::class, 'sectBuy']);
        Route::post('battle/prepare', [BattleRuntimeController::class, 'prepare']);
        Route::post('battle/settle', [BattleRuntimeController::class, 'settle']);
    });
});
