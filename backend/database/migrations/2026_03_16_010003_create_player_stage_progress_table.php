<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_stage_progress', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->string('chapter_id');
            $table->string('node_id');
            $table->string('difficulty_id');
            $table->boolean('is_first_clear')->default(false);
            $table->unsignedInteger('clear_count')->default(0);
            $table->timestamps();

            $table->unique(['player_id', 'node_id', 'difficulty_id']);
            $table->index(['player_id', 'chapter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_stage_progress');
    }
};
