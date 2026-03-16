<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id')->unique();
            $table->string('nickname');
            $table->string('auth_token', 120)->nullable()->unique();
            $table->string('class_id')->nullable();
            $table->unsignedInteger('level')->default(1);
            $table->unsignedInteger('exp')->default(0);
            $table->unsignedInteger('power')->default(0);
            $table->unsignedInteger('gold')->default(0);
            $table->unsignedInteger('jade')->default(0);
            $table->unsignedInteger('contribution')->default(0);
            $table->string('current_chapter_id')->nullable();
            $table->string('current_node_id')->nullable();
            $table->unsignedInteger('max_hp')->default(850);
            $table->unsignedInteger('max_energy')->default(100);
            $table->unsignedInteger('skill_points')->default(0);
            $table->json('skill_levels')->nullable();
            $table->json('equipment_summary')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_profiles');
    }
};
