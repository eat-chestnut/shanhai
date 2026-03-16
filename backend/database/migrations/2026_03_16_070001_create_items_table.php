<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->string('item_id')->primary()->comment('物品ID');
            $table->string('item_name')->comment('物品名称');
            $table->string('item_type')->comment('物品类型');
            $table->string('rarity')->comment('稀有度');
            $table->string('icon')->nullable()->comment('图标');
            $table->text('desc')->nullable()->comment('描述');
            $table->boolean('is_enabled')->default(true)->comment('是否启用');
            $table->timestamps();
            
            $table->index('item_type');
            $table->index('rarity');
            $table->index('is_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
