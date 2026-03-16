<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_scripture_progress', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id')->index();
            $table->string('scripture_id')->index();
            $table->unsignedInteger('current_world_level')->default(0);
            $table->unsignedInteger('max_unlocked_world_level')->default(0);
            $table->timestamps();

            $table->unique(['player_id', 'scripture_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_scripture_progress');
    }
};
