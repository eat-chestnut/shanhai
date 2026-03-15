<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mainline_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('node_id')->unique();
            $table->string('chapter_id');
            $table->string('node_name')->index();
            $table->json('unlock_condition');
            $table->json('difficulty_ids');
            $table->timestamps();

            $table->foreign('chapter_id')
                ->references('chapter_id')
                ->on('mainline_chapters')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mainline_nodes');
    }
};
