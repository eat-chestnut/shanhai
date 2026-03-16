<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dungeon_difficulties', function (Blueprint $table) {
            // 普通怪配置
            $table->json('normal_monster_ids')->nullable()->comment('普通怪物ID列表');
            $table->integer('normal_spawn_interval')->default(3)->comment('普通怪刷新间隔（秒）');
            $table->integer('normal_spawn_count')->default(2)->comment('每次刷新普通怪数量');
            $table->integer('normal_alive_limit')->default(6)->comment('普通怪同时存在上限');
            
            // 精英怪配置
            $table->json('elite_monster_ids')->nullable()->comment('精英怪物ID列表');
            $table->integer('elite_spawn_interval')->default(6)->comment('精英怪刷新间隔（秒）');
            $table->integer('elite_spawn_count')->default(1)->comment('每次刷新精英怪数量');
            $table->integer('elite_alive_limit')->default(1)->comment('精英怪同时存在上限');
            
            // Boss配置
            $table->string('boss_monster_id')->nullable()->comment('Boss怪物ID');
            
            // 阶段触发条件
            $table->integer('normal_kill_to_spawn_elite')->default(12)->comment('击杀普通怪数量触发精英怪');
            $table->integer('elite_kill_to_spawn_boss')->default(3)->comment('击杀精英怪数量触发Boss');
            
            // 流程控制
            $table->boolean('stop_spawn_after_boss_appears')->default(true)->comment('Boss出现后停止刷怪');
            $table->boolean('clear_on_boss_killed')->default(true)->comment('Boss被击杀后通关');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dungeon_difficulties', function (Blueprint $table) {
            $table->dropColumn([
                'normal_monster_ids',
                'normal_spawn_interval', 
                'normal_spawn_count',
                'normal_alive_limit',
                'elite_monster_ids',
                'elite_spawn_interval',
                'elite_spawn_count', 
                'elite_alive_limit',
                'boss_monster_id',
                'normal_kill_to_spawn_elite',
                'elite_kill_to_spawn_boss',
                'stop_spawn_after_boss_appears',
                'clear_on_boss_killed'
            ]);
        });
    }
};
