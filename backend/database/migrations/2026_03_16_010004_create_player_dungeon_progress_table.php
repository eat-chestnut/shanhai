<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_dungeon_progress', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->string('dungeon_id');
            $table->string('difficulty_id');
            $table->boolean('is_first_clear')->default(false);
            $table->unsignedInteger('clear_count')->default(0);
            $table->unsignedInteger('daily_count')->default(0);
            $table->timestamps();

            $table->unique(['player_id', 'dungeon_id', 'difficulty_id']);
            $table->index('player_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_dungeon_progress');
    }
};
