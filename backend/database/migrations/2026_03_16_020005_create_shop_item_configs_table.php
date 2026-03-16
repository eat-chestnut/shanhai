<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_item_configs', function (Blueprint $table): void {
            $table->id();
            $table->string('shop_item_id')->unique();
            $table->string('shop_type');
            $table->string('item_id');
            $table->string('item_name');
            $table->unsignedInteger('count')->default(1);
            $table->string('cost_type');
            $table->unsignedInteger('cost_value')->default(0);
            $table->unsignedInteger('buy_limit')->default(0);
            $table->string('cycle_type')->default('lifetime');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_open')->default(true);
            $table->timestamps();

            $table->index(['shop_type', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_item_configs');
    }
};
