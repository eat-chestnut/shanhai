<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dungeon_difficulties', function (Blueprint $table) {
            $table->json('normal_monster_pool')->nullable()->after('first_clear_reward_group_id')->comment('普通怪物池');
            $table->json('elite_monster_pool')->nullable()->after('normal_monster_pool')->comment('精英怪物池');
            $table->json('boss_monster_pool')->nullable()->after('elite_monster_pool')->comment('Boss怪物池');
            $table->unsignedInteger('normal_spawn_interval')->default(5)->after('boss_monster_pool')->comment('普通怪刷新间隔(秒)');
            $table->unsignedInteger('normal_spawn_count')->default(1)->after('normal_spawn_interval')->comment('单次刷新普通怪数量');
            $table->unsignedInteger('max_normal_on_screen')->default(5)->after('normal_spawn_count')->comment('同屏普通怪上限');
            $table->unsignedInteger('elite_trigger_kills')->default(10)->after('max_normal_on_screen')->comment('击杀多少普通怪后出现精英怪');
            $table->unsignedInteger('boss_trigger_elite_kills')->default(3)->after('elite_trigger_kills')->comment('击杀多少精英怪后出现Boss');
            $table->boolean('stop_spawning_after_boss')->default(true)->after('boss_trigger_elite_kills')->comment('Boss出现后停止刷新其他怪物');
            $table->boolean('clear_dungeon_after_boss')->default(true)->after('stop_spawning_after_boss')->comment('Boss击杀后通关');
        });
    }

    public function down(): void
    {
        Schema::table('dungeon_difficulties', function (Blueprint $table) {
            $table->dropColumn([
                'normal_monster_pool',
                'elite_monster_pool',
                'boss_monster_pool',
                'normal_spawn_interval',
                'normal_spawn_count',
                'max_normal_on_screen',
                'elite_trigger_kills',
                'boss_trigger_elite_kills',
                'stop_spawning_after_boss',
                'clear_dungeon_after_boss'
            ]);
        });
    }
};
