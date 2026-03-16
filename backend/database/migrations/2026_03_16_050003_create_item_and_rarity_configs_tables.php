<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        Schema::create('rarity_configs', function (Blueprint $table) {
            $table->id();
            $table->string('rarity_key')->unique()->comment('稀有度标识');
            $table->string('rarity_name')->comment('稀有度名称');
            $table->unsignedInteger('sort_order')->default(0)->comment('排序');
            $table->string('text_color')->nullable()->comment('文字颜色');
            $table->string('bg_color')->nullable()->comment('背景颜色');
            $table->string('border_color')->nullable()->comment('边框颜色');
            $table->string('glow_color')->nullable()->comment('发光颜色');
            $table->string('frame_key')->nullable()->comment('边框资源Key');
            $table->boolean('is_enabled')->default(true)->comment('是否启用');
            $table->timestamps();
            
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_configs');
        Schema::dropIfExists('rarity_configs');
    }
};
