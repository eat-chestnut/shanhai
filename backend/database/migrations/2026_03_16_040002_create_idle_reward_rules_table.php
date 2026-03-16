<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idle_reward_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('rule_id')->unique();
            $table->string('rule_name');
            $table->unsignedInteger('min_level')->default(1);
            $table->unsignedInteger('max_level')->default(999);
            $table->unsignedInteger('idle_cap_hours')->default(12);
            $table->json('reward_rate');
            $table->string('bonus_hint')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_open')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idle_reward_rules');
    }
};
