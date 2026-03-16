<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mainline_chapters', function (Blueprint $table): void {
            $table->string('scripture_id')->nullable()->after('chapter_name')->index();
        });
    }

    public function down(): void
    {
        Schema::table('mainline_chapters', function (Blueprint $table): void {
            $table->dropColumn('scripture_id');
        });
    }
};
