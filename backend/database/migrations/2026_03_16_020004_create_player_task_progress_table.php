<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_task_progress', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->string('task_id');
            $table->string('cycle_key')->default('permanent');
            $table->unsignedInteger('progress')->default(0);
            $table->boolean('is_claimed')->default(false);
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();

            $table->unique(['player_id', 'task_id', 'cycle_key']);
            $table->index(['player_id', 'cycle_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_task_progress');
    }
};
