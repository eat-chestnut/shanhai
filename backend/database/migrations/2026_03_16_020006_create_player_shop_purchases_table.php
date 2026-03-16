<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_shop_purchases', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->string('shop_item_id');
            $table->string('cycle_key')->default('permanent');
            $table->unsignedInteger('bought_count')->default(0);
            $table->timestamps();

            $table->unique(['player_id', 'shop_item_id', 'cycle_key']);
            $table->index(['player_id', 'cycle_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_shop_purchases');
    }
};
