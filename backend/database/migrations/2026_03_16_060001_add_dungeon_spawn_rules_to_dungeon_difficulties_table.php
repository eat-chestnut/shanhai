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
            if (! Schema::hasColumn('dungeon_difficulties', 'normal_monster_ids')) {
                $table->json('normal_monster_ids')->nullable()->comment('普通怪物ID列表');
            }
            if (! Schema::hasColumn('dungeon_difficulties', 'normal_spawn_interval')) {
                $table->integer('normal_spawn_interval')->default(3)->comment('普通怪刷新间隔（秒）');
            }
            if (! Schema::hasColumn('dungeon_difficulties', 'normal_spawn_count')) {
                $table->integer('normal_spawn_count')->default(2)->comment('每次刷新普通怪数量');
            }
            if (! Schema::hasColumn('dungeon_difficulties', 'normal_alive_limit')) {
                $table->integer('normal_alive_limit')->default(6)->comment('普通怪同时存在上限');
            }

            if (! Schema::hasColumn('dungeon_difficulties', 'elite_monster_ids')) {
                $table->json('elite_monster_ids')->nullable()->comment('精英怪物ID列表');
            }
            if (! Schema::hasColumn('dungeon_difficulties', 'elite_spawn_interval')) {
                $table->integer('elite_spawn_interval')->default(6)->comment('精英怪刷新间隔（秒）');
            }
            if (! Schema::hasColumn('dungeon_difficulties', 'elite_spawn_count')) {
                $table->integer('elite_spawn_count')->default(1)->comment('每次刷新精英怪数量');
            }
            if (! Schema::hasColumn('dungeon_difficulties', 'elite_alive_limit')) {
                $table->integer('elite_alive_limit')->default(1)->comment('精英怪同时存在上限');
            }

            if (! Schema::hasColumn('dungeon_difficulties', 'boss_monster_id')) {
                $table->string('boss_monster_id')->nullable()->comment('Boss怪物ID');
            }
            if (! Schema::hasColumn('dungeon_difficulties', 'normal_kill_to_spawn_elite')) {
                $table->integer('normal_kill_to_spawn_elite')->default(12)->comment('击杀普通怪数量触发精英怪');
            }
            if (! Schema::hasColumn('dungeon_difficulties', 'elite_kill_to_spawn_boss')) {
                $table->integer('elite_kill_to_spawn_boss')->default(3)->comment('击杀精英怪数量触发Boss');
            }
            if (! Schema::hasColumn('dungeon_difficulties', 'stop_spawn_after_boss_appears')) {
                $table->boolean('stop_spawn_after_boss_appears')->default(true)->comment('Boss出现后停止刷怪');
            }
            if (! Schema::hasColumn('dungeon_difficulties', 'clear_on_boss_killed')) {
                $table->boolean('clear_on_boss_killed')->default(true)->comment('Boss被击杀后通关');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dungeon_difficulties', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('dungeon_difficulties', 'normal_monster_ids') ? 'normal_monster_ids' : null,
                Schema::hasColumn('dungeon_difficulties', 'normal_spawn_interval') ? 'normal_spawn_interval' : null,
                Schema::hasColumn('dungeon_difficulties', 'normal_spawn_count') ? 'normal_spawn_count' : null,
                Schema::hasColumn('dungeon_difficulties', 'normal_alive_limit') ? 'normal_alive_limit' : null,
                Schema::hasColumn('dungeon_difficulties', 'elite_monster_ids') ? 'elite_monster_ids' : null,
                Schema::hasColumn('dungeon_difficulties', 'elite_spawn_interval') ? 'elite_spawn_interval' : null,
                Schema::hasColumn('dungeon_difficulties', 'elite_spawn_count') ? 'elite_spawn_count' : null,
                Schema::hasColumn('dungeon_difficulties', 'elite_alive_limit') ? 'elite_alive_limit' : null,
                Schema::hasColumn('dungeon_difficulties', 'boss_monster_id') ? 'boss_monster_id' : null,
                Schema::hasColumn('dungeon_difficulties', 'normal_kill_to_spawn_elite') ? 'normal_kill_to_spawn_elite' : null,
                Schema::hasColumn('dungeon_difficulties', 'elite_kill_to_spawn_boss') ? 'elite_kill_to_spawn_boss' : null,
                Schema::hasColumn('dungeon_difficulties', 'stop_spawn_after_boss_appears') ? 'stop_spawn_after_boss_appears' : null,
                Schema::hasColumn('dungeon_difficulties', 'clear_on_boss_killed') ? 'clear_on_boss_killed' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
