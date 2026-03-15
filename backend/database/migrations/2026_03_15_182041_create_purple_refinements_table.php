<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purple_refinements', function (Blueprint $table) {
            $table->id();
            $table->string('refinement_id')->unique();
            $table->string('name')->index();
            $table->json('bonuses');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purple_refinements');
    }
};
