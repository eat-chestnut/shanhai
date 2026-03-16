<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mainline_chapters', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('unlock_level')->comment('排序');
            $table->string('required_previous_chapter')->nullable()->after('sort_order')->comment('需要的前置章节ID');
            $table->string('required_previous_highest_difficulty')->nullable()->after('required_previous_chapter')->comment('需要前置章节的最高难度 difficulty_id');
        });
    }

    public function down(): void
    {
        Schema::table('mainline_chapters', function (Blueprint $table) {
            $table->dropColumn([
                'sort_order',
                'required_previous_chapter',
                'required_previous_highest_difficulty'
            ]);
        });
    }
};
