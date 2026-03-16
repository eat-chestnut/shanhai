<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_profiles', function (Blueprint $table): void {
            $table->timestamp('idle_started_at')->nullable()->after('last_login_at');
            $table->timestamp('idle_last_claimed_at')->nullable()->after('idle_started_at');
            $table->timestamp('last_active_at')->nullable()->after('idle_last_claimed_at');
        });
    }

    public function down(): void
    {
        Schema::table('player_profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'idle_started_at',
                'idle_last_claimed_at',
                'last_active_at',
            ]);
        });
    }
};
