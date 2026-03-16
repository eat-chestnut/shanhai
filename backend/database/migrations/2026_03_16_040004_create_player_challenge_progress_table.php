<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_challenge_progress', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->string('challenge_id');
            $table->string('week_key')->nullable();
            $table->unsignedInteger('highest_floor')->default(0);
            $table->unsignedInteger('current_floor')->default(1);
            $table->unsignedInteger('weekly_highest_floor')->default(0);
            $table->unsignedInteger('clear_count')->default(0);
            $table->unsignedInteger('weekly_clear_count')->default(0);
            $table->json('first_clear_floors')->nullable();
            $table->json('weekly_reward_claimed_floors')->nullable();
            $table->unsignedInteger('last_cleared_floor')->default(0);
            $table->timestamp('last_cleared_at')->nullable();
            $table->timestamps();

            $table->unique(['player_id', 'challenge_id']);
            $table->index(['player_id', 'challenge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_challenge_progress');
    }
};
