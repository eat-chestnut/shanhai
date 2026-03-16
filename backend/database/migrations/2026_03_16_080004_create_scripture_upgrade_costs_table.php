<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scripture_upgrade_costs', function (Blueprint $table): void {
            $table->id();
            $table->string('scripture_id')->index();
            $table->unsignedInteger('target_world_level')->index();
            $table->json('cost_items');
            $table->unsignedInteger('cost_gold')->default(0);
            $table->unsignedInteger('required_player_level')->default(1);
            $table->timestamps();

            $table->unique(['scripture_id', 'target_world_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scripture_upgrade_costs');
    }
};
