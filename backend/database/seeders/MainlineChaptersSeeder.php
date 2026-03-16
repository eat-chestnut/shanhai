<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MainlineChaptersSeeder extends Seeder
{
    public function run(): void
    {
        $chapters = [
            [
                'chapter_id' => 'prologue',
                'chapter_name' => '序章',
                'unlock_level' => 1,
                'sort_order' => 0,
                'required_previous_chapter' => null,
                'required_previous_highest_difficulty' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chapter_id' => 'chapter_01',
                'chapter_name' => '第一章',
                'unlock_level' => 5,
                'sort_order' => 1,
                'required_previous_chapter' => 'prologue',
                'required_previous_highest_difficulty' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chapter_id' => 'chapter_02',
                'chapter_name' => '第二章',
                'unlock_level' => 10,
                'sort_order' => 2,
                'required_previous_chapter' => 'chapter_01',
                'required_previous_highest_difficulty' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chapter_id' => 'chapter_03',
                'chapter_name' => '第三章',
                'unlock_level' => 15,
                'sort_order' => 3,
                'required_previous_chapter' => 'chapter_02',
                'required_previous_highest_difficulty' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chapter_id' => 'chapter_04',
                'chapter_name' => '第四章',
                'unlock_level' => 20,
                'sort_order' => 4,
                'required_previous_chapter' => 'chapter_03',
                'required_previous_highest_difficulty' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chapter_id' => 'chapter_05',
                'chapter_name' => '第五章',
                'unlock_level' => 25,
                'sort_order' => 5,
                'required_previous_chapter' => 'chapter_04',
                'required_previous_highest_difficulty' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chapter_id' => 'chapter_06',
                'chapter_name' => '第六章',
                'unlock_level' => 30,
                'sort_order' => 6,
                'required_previous_chapter' => 'chapter_05',
                'required_previous_highest_difficulty' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chapter_id' => 'chapter_07',
                'chapter_name' => '第七章',
                'unlock_level' => 35,
                'sort_order' => 7,
                'required_previous_chapter' => 'chapter_06',
                'required_previous_highest_difficulty' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chapter_id' => 'chapter_08',
                'chapter_name' => '第八章',
                'unlock_level' => 40,
                'sort_order' => 8,
                'required_previous_chapter' => 'chapter_07',
                'required_previous_highest_difficulty' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chapter_id' => 'epilogue',
                'chapter_name' => '终章',
                'unlock_level' => 45,
                'sort_order' => 9,
                'required_previous_chapter' => 'chapter_08',
                'required_previous_highest_difficulty' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('mainline_chapters')->insertOrIgnore($chapters);
    }
}
