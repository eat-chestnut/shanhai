<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scripture_chapter_bindings', function (Blueprint $table): void {
            $table->id();
            $table->string('scripture_id')->index();
            $table->string('chapter_id')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->unique(['scripture_id', 'chapter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scripture_chapter_bindings');
    }
};
