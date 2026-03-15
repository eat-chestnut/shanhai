<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gems', function (Blueprint $table) {
            $table->id();
            $table->string('gem_id')->unique();
            $table->string('name')->index();
            $table->string('type')->index();
            $table->integer('bonus_atk')->default(0);
            $table->integer('bonus_boss_dmg')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gems');
    }
};
