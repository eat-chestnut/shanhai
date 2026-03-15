<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monsters', function (Blueprint $table) {
            $table->id();
            $table->string('monster_id')->unique();
            $table->string('name')->index();
            $table->unsignedBigInteger('base_hp')->default(0);
            $table->unsignedBigInteger('base_atk')->default(0);
            $table->boolean('is_boss')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monsters');
    }
};
