<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('battle_records', function (Blueprint $table): void {
            $table->id();
            $table->string('battle_id')->unique();
            $table->unsignedBigInteger('player_id');
            $table->string('source_type');
            $table->string('source_id');
            $table->string('difficulty_id');
            $table->string('status')->default('prepared');
            $table->string('result')->nullable();
            $table->unsignedInteger('duration')->default(0);
            $table->unsignedInteger('cleared_wave')->default(0);
            $table->string('battle_map_id');
            $table->unsignedInteger('battle_seed');
            $table->json('request_snapshot')->nullable();
            $table->json('player_snapshot')->nullable();
            $table->json('enemy_group_snapshot')->nullable();
            $table->json('settle_payload')->nullable();
            $table->json('rewards')->nullable();
            $table->json('first_clear_rewards')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->index(['player_id', 'source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('battle_records');
    }
};
