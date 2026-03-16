<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scriptures', function (Blueprint $table): void {
            $table->string('scripture_id')->primary();
            $table->string('scripture_name')->index();
            $table->string('scripture_group')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->json('unlock_condition');
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scriptures');
    }
};
