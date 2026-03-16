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
        Schema::create('rarity_configs', function (Blueprint $table) {
            $table->string('rarity_key')->primary()->comment('稀有度键值');
            $table->string('rarity_name')->comment('稀有度名称');
            $table->integer('sort')->default(0)->comment('排序');
            $table->string('text_color')->default('#FFFFFF')->comment('文字颜色');
            $table->string('bg_color')->default('#2F2F2F')->comment('背景颜色');
            $table->string('border_color')->default('#7A7A7A')->comment('边框颜色');
            $table->string('frame_key')->nullable()->comment('边框样式键值');
            $table->boolean('is_enabled')->default(true)->comment('是否启用');
            $table->timestamps();
            
            $table->index('sort');
            $table->index('is_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rarity_configs');
    }
};
