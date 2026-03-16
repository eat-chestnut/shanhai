<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('item_configs')) {
            Schema::create('item_configs', function (Blueprint $table) {
                $table->id();
                $table->string('item_id')->unique()->comment('物品ID');
                $table->string('item_name')->index()->comment('物品名称');
                $table->string('item_type')->index()->comment('物品类型');
                $table->string('rarity')->default('common')->index()->comment('稀有度');
                $table->string('icon')->nullable()->comment('图标');
                $table->text('description')->nullable()->comment('描述');
                $table->boolean('is_enabled')->default(true)->index()->comment('是否启用');
                $table->json('extra_data')->nullable()->comment('额外数据');
                $table->timestamps();

                $table->index(['item_type', 'rarity']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('item_configs');
    }
};
