<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_equipments', function (Blueprint $table): void {
            $table->id();
            $table->string('equipment_uid')->unique();
            $table->unsignedBigInteger('player_id');
            $table->string('equip_id');
            $table->string('slot_type');
            $table->unsignedInteger('star_level')->default(0);
            $table->json('gem_slots_json')->nullable();
            $table->string('blue_affix_id')->nullable();
            $table->string('purple_refinement_id')->nullable();
            $table->boolean('is_equipped')->default(false);
            $table->timestamps();

            $table->index(['player_id', 'slot_type']);
            $table->index(['player_id', 'equip_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_equipments');
    }
};
