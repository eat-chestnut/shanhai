<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mainline_difficulties', function (Blueprint $table) {
            $table->id();
            $table->string('difficulty_id');
            $table->string('node_id');
            $table->unsignedInteger('recommended_power')->default(0);
            $table->string('first_clear_reward_group_id')->nullable()->index();
            $table->timestamps();

            $table->unique(['node_id', 'difficulty_id']);
            $table->foreign('node_id')
                ->references('node_id')
                ->on('mainline_nodes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mainline_difficulties');
    }
};
