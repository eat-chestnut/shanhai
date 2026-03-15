<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('equip_id')->unique();
            $table->string('name')->index();
            $table->string('type')->index();
            $table->unsignedInteger('level')->default(1)->index();
            $table->unsignedInteger('base_atk')->default(0);
            $table->unsignedInteger('base_def')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
