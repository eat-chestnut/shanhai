<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monsters', function (Blueprint $table): void {
            $table->string('combat_role')->nullable()->after('name');
            $table->json('behavior_profile')->nullable()->after('is_boss');
        });
    }

    public function down(): void
    {
        Schema::table('monsters', function (Blueprint $table): void {
            $table->dropColumn([
                'combat_role',
                'behavior_profile',
            ]);
        });
    }
};
