<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scripture_world_tiers', function (Blueprint $table): void {
            $table->id();
            $table->string('scripture_id')->index();
            $table->unsignedInteger('world_level_start')->index();
            $table->unsignedInteger('world_level_end')->index();
            $table->decimal('hp_scale', 8, 4)->default(1);
            $table->decimal('atk_scale', 8, 4)->default(1);
            $table->decimal('def_scale', 8, 4)->default(1);
            $table->decimal('reward_scale', 8, 4)->default(1);
            $table->decimal('gold_scale', 8, 4)->default(1);
            $table->json('normal_monster_ids');
            $table->json('elite_monster_ids');
            $table->json('boss_monster_ids');
            $table->json('extra_drop_tags');
            $table->string('new_feature_note')->nullable();
            $table->timestamps();

            $table->unique(['scripture_id', 'world_level_start', 'world_level_end'], 'scripture_world_tiers_scope_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scripture_world_tiers');
    }
};
