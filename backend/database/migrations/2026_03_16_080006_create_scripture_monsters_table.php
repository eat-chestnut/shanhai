<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scripture_monsters', function (Blueprint $table): void {
            $table->string('monster_id')->primary();
            $table->string('name')->index();
            $table->string('monster_type')->index();
            $table->string('race')->index();
            $table->string('rarity')->index();
            $table->unsignedBigInteger('base_hp')->default(0);
            $table->unsignedBigInteger('base_atk')->default(0);
            $table->unsignedBigInteger('base_def')->default(0);
            $table->unsignedInteger('move_speed')->default(0);
            $table->string('ai_type')->nullable();
            $table->json('skill_ids');
            $table->boolean('is_boss')->default(false)->index();
            $table->boolean('is_elite')->default(false)->index();
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scripture_monsters');
    }
};
