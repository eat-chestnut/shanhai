<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hall_features', function (Blueprint $table) {
            $table->id();
            $table->string('feature_id')->unique();
            $table->string('feature_name')->index();
            $table->string('feature_type')->index();
            $table->json('unlock_condition');
            $table->json('jump_target');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hall_features');
    }
};
