<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dungeon_difficulties', function (Blueprint $table): void {
            $table->string('first_clear_reward_group_id')->nullable()->after('recommended_power');
        });
    }

    public function down(): void
    {
        Schema::table('dungeon_difficulties', function (Blueprint $table): void {
            $table->dropColumn('first_clear_reward_group_id');
        });
    }
};
