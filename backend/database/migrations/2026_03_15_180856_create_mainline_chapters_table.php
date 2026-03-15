<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mainline_chapters', function (Blueprint $table) {
            $table->id();
            $table->string('chapter_id')->unique();
            $table->string('chapter_name')->index();
            $table->unsignedInteger('unlock_level')->default(1)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mainline_chapters');
    }
};
