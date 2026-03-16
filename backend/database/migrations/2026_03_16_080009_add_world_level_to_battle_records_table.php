<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('battle_records', function (Blueprint $table): void {
            $table->unsignedInteger('world_level')->nullable()->after('difficulty_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('battle_records', function (Blueprint $table): void {
            $table->dropColumn('world_level');
        });
    }
};
