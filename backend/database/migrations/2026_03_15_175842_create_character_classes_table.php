<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_classes', function (Blueprint $table) {
            $table->id();
            $table->string('class_id')->unique();
            $table->string('class_name')->index();
            $table->text('class_desc')->nullable();
            $table->string('role_type')->index();
            $table->boolean('is_open')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_classes');
    }
};
