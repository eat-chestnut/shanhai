<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_dungeon_progress', function (Blueprint $table): void {
            $table->timestamp('daily_reset_at')->nullable()->after('daily_count');
        });
    }

    public function down(): void
    {
        Schema::table('player_dungeon_progress', function (Blueprint $table): void {
            $table->dropColumn('daily_reset_at');
        });
    }
};
