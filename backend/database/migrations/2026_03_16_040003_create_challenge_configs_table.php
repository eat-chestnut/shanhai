<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_configs', function (Blueprint $table): void {
            $table->id();
            $table->string('challenge_id')->unique();
            $table->string('challenge_name');
            $table->string('challenge_type')->default('tower');
            $table->text('challenge_desc')->nullable();
            $table->unsignedInteger('unlock_level')->default(60);
            $table->string('cycle_type')->default('weekly');
            $table->json('floors');
            $table->json('reward_preview')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_open')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_configs');
    }
};
