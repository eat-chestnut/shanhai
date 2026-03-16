<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_configs', function (Blueprint $table): void {
            $table->id();
            $table->string('task_id')->unique();
            $table->string('task_type');
            $table->string('task_name');
            $table->string('task_desc', 255)->default('');
            $table->string('target_type');
            $table->unsignedInteger('target')->default(1);
            $table->json('conditions')->nullable();
            $table->json('rewards')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_open')->default(true);
            $table->timestamps();

            $table->index(['task_type', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_configs');
    }
};
