<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('skill_id')->unique();
            $table->string('class_id');
            $table->string('skill_name');
            $table->text('skill_desc')->nullable();
            $table->string('type');
            $table->string('effect_type')->nullable();
            $table->string('target_type')->nullable();
            $table->unsignedInteger('cooldown')->default(0);
            $table->unsignedInteger('cost')->default(0);
            $table->unsignedInteger('unlock_level')->default(1);
            $table->unsignedInteger('max_level')->default(5);
            $table->unsignedInteger('power_base')->default(0);
            $table->unsignedInteger('power_per_level')->default(0);
            $table->unsignedInteger('duration')->default(0);
            $table->decimal('chance', 5, 4)->default(0);
            $table->json('stat_bonuses')->nullable();
            $table->json('effect_payload')->nullable();
            $table->boolean('is_open')->default(true);
            $table->timestamps();

            $table->foreign('class_id')
                ->references('class_id')
                ->on('character_classes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skills');
    }
};
