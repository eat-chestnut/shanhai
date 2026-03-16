<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dungeons', function (Blueprint $table): void {
            $table->text('dungeon_desc')->nullable()->after('dungeon_name');
            $table->json('main_rewards')->nullable()->after('unlock_level');
            $table->unsignedInteger('daily_limit')->default(3)->after('main_rewards');
            $table->string('unlock_stage_node_id')->nullable()->after('daily_limit');
        });
    }

    public function down(): void
    {
        Schema::table('dungeons', function (Blueprint $table): void {
            $table->dropColumn([
                'dungeon_desc',
                'main_rewards',
                'daily_limit',
                'unlock_stage_node_id',
            ]);
        });
    }
};
