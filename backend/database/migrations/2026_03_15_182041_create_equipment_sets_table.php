<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_sets', function (Blueprint $table) {
            $table->id();
            $table->string('set_id')->unique();
            $table->unsignedInteger('level')->default(1)->index();
            $table->json('pieces');
            $table->json('effects');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_sets');
    }
};
