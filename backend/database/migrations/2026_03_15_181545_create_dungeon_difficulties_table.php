<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dungeon_difficulties', function (Blueprint $table) {
            $table->id();
            $table->string('difficulty_id');
            $table->string('dungeon_id');
            $table->unsignedInteger('recommended_power')->default(0);
            $table->timestamps();

            $table->unique(['dungeon_id', 'difficulty_id']);
            $table->foreign('dungeon_id')
                ->references('dungeon_id')
                ->on('dungeons')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dungeon_difficulties');
    }
};
