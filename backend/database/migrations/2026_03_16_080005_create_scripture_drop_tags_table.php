<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scripture_drop_tags', function (Blueprint $table): void {
            $table->string('drop_tag')->primary();
            $table->string('tag_name');
            $table->json('items');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scripture_drop_tags');
    }
};
