<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monster_drops', function (Blueprint $table) {
            $table->id();
            $table->string('monster_id');
            $table->string('item_id');
            $table->decimal('drop_rate', 8, 4)->default(0);
            $table->string('drop_kind')->default('normal')->index();
            $table->timestamps();

            $table->unique(['monster_id', 'item_id']);
            $table->foreign('monster_id')
                ->references('monster_id')
                ->on('monsters')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monster_drops');
    }
};
