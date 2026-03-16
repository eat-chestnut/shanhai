<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\MainlineChapter;
use App\Models\MainlineNode;
use App\Models\MainlineDifficulty;

class MainlineContentSeeder extends Seeder
{
    public function run(): void
    {
        // 清空现有数据
        DB::table('mainline_difficulties')->delete();
        DB::table('mainline_nodes')->delete();
        DB::table('mainline_chapters')->delete();

        // 章节数据
        $chapters = [
            [
                'chapter_id' => 'prologue',
                'chapter_name' => '序章·奉命巡山',
                'unlock_level' => 1,
                'sort_order' => 1,
                'required_previous_chapter' => null,
                'required_previous_highest_difficulty' => null
            ],
            [
                'chapter_id' => 'chapter_01',
                'chapter_name' => '第一章·南山初行',
                'unlock_level' => 3,
                'sort_order' => 2,
                'required_previous_chapter' => 'prologue',
                'required_previous_highest_difficulty' => 3
            ],
            [
                'chapter_id' => 'chapter_02',
                'chapter_name' => '第二章·山林幽行',
                'unlock_level' => 8,
                'sort_order' => 3,
                'required_previous_chapter' => 'chapter_01',
                'required_previous_highest_difficulty' => 3
            ],
            [
                'chapter_id' => 'chapter_03',
                'chapter_name' => '第三章·群峰异动',
                'unlock_level' => 14,
                'sort_order' => 4,
                'required_previous_chapter' => 'chapter_02',
                'required_previous_highest_difficulty' => 3
            ],
            [
                'chapter_id' => 'chapter_04',
                'chapter_name' => '第四章·古岭妖踪',
                'unlock_level' => 20,
                'sort_order' => 5,
                'required_previous_chapter' => 'chapter_03',
                'required_previous_highest_difficulty' => 3
            ],
            [
                'chapter_id' => 'chapter_05',
                'chapter_name' => '第五章·深谷迷踪',
                'unlock_level' => 28,
                'sort_order' => 6,
                'required_previous_chapter' => 'chapter_04',
                'required_previous_highest_difficulty' => 3
            ],
            [
                'chapter_id' => 'chapter_06',
                'chapter_name' => '第六章·云巅震鸣',
                'unlock_level' => 36,
                'sort_order' => 7,
                'required_previous_chapter' => 'chapter_05',
                'required_previous_highest_difficulty' => 3
            ],
            [
                'chapter_id' => 'chapter_07',
                'chapter_name' => '第七章·百妖竞起',
                'unlock_level' => 44,
                'sort_order' => 8,
                'required_previous_chapter' => 'chapter_06',
                'required_previous_highest_difficulty' => 3
            ],
            [
                'chapter_id' => 'chapter_08',
                'chapter_name' => '第八章·群山震动',
                'unlock_level' => 52,
                'sort_order' => 9,
                'required_previous_chapter' => 'chapter_07',
                'required_previous_highest_difficulty' => 3
            ],
            [
                'chapter_id' => 'epilogue',
                'chapter_name' => '终章·巡厄终局',
                'unlock_level' => 60,
                'sort_order' => 10,
                'required_previous_chapter' => 'chapter_08',
                'required_previous_highest_difficulty' => 3
            ]
        ];

        // 节点数据
        $nodes = [
            // 序章节点
            [
                'node_id' => 'node_prologue_01',
                'chapter_id' => 'prologue',
                'node_name' => '山门外径',
                'unlock_condition' => json_encode(['level' => 1, 'clear_node_id' => null, 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_prologue_01_easy', 'diff_node_prologue_01_normal', 'diff_node_prologue_01_hard'])
            ],
            [
                'node_id' => 'node_prologue_02',
                'chapter_id' => 'prologue',
                'node_name' => '残碑古道',
                'unlock_condition' => json_encode(['level' => 1, 'clear_node_id' => 'node_prologue_01', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_prologue_02_easy', 'diff_node_prologue_02_normal', 'diff_node_prologue_02_hard'])
            ],
            [
                'node_id' => 'node_prologue_03',
                'chapter_id' => 'prologue',
                'node_name' => '雾岭前哨',
                'unlock_condition' => json_encode(['level' => 2, 'clear_node_id' => 'node_prologue_02', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_prologue_03_easy', 'diff_node_prologue_03_normal', 'diff_node_prologue_03_hard'])
            ],

            // 第一章节点
            [
                'node_id' => 'node_ch01_01',
                'chapter_id' => 'chapter_01',
                'node_name' => '林间栈道',
                'unlock_condition' => json_encode(['level' => 3, 'clear_node_id' => null, 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch01_01_easy', 'diff_node_ch01_01_normal', 'diff_node_ch01_01_hard'])
            ],
            [
                'node_id' => 'node_ch01_02',
                'chapter_id' => 'chapter_01',
                'node_name' => '荒木坡地',
                'unlock_condition' => json_encode(['level' => 4, 'clear_node_id' => 'node_ch01_01', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch01_02_easy', 'diff_node_ch01_02_normal', 'diff_node_ch01_02_hard'])
            ],
            [
                'node_id' => 'node_ch01_03',
                'chapter_id' => 'chapter_01',
                'node_name' => '南山石窟',
                'unlock_condition' => json_encode(['level' => 5, 'clear_node_id' => 'node_ch01_02', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch01_03_easy', 'diff_node_ch01_03_normal', 'diff_node_ch01_03_hard'])
            ],

            // 第二章节点
            [
                'node_id' => 'node_ch02_01',
                'chapter_id' => 'chapter_02',
                'node_name' => '密林浅层',
                'unlock_condition' => json_encode(['level' => 8, 'clear_node_id' => null, 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch02_01_easy', 'diff_node_ch02_01_normal', 'diff_node_ch02_01_hard'])
            ],
            [
                'node_id' => 'node_ch02_02',
                'chapter_id' => 'chapter_02',
                'node_name' => '藤蔓回廊',
                'unlock_condition' => json_encode(['level' => 9, 'clear_node_id' => 'node_ch02_01', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch02_02_easy', 'diff_node_ch02_02_normal', 'diff_node_ch02_02_hard'])
            ],
            [
                'node_id' => 'node_ch02_03',
                'chapter_id' => 'chapter_02',
                'node_name' => '黑叶深林',
                'unlock_condition' => json_encode(['level' => 10, 'clear_node_id' => 'node_ch02_02', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch02_03_easy', 'diff_node_ch02_03_normal', 'diff_node_ch02_03_hard'])
            ],

            // 第三章节点
            [
                'node_id' => 'node_ch03_01',
                'chapter_id' => 'chapter_03',
                'node_name' => '断崖峰道',
                'unlock_condition' => json_encode(['level' => 14, 'clear_node_id' => null, 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch03_01_easy', 'diff_node_ch03_01_normal', 'diff_node_ch03_01_hard'])
            ],
            [
                'node_id' => 'node_ch03_02',
                'chapter_id' => 'chapter_03',
                'node_name' => '乱石高坡',
                'unlock_condition' => json_encode(['level' => 15, 'clear_node_id' => 'node_ch03_01', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch03_02_easy', 'diff_node_ch03_02_normal', 'diff_node_ch03_02_hard'])
            ],
            [
                'node_id' => 'node_ch03_03',
                'chapter_id' => 'chapter_03',
                'node_name' => '群峰试炼场',
                'unlock_condition' => json_encode(['level' => 16, 'clear_node_id' => 'node_ch03_02', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch03_03_easy', 'diff_node_ch03_03_normal', 'diff_node_ch03_03_hard'])
            ],

            // 第四章节点
            [
                'node_id' => 'node_ch04_01',
                'chapter_id' => 'chapter_04',
                'node_name' => '古岭前庭',
                'unlock_condition' => json_encode(['level' => 20, 'clear_node_id' => null, 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch04_01_easy', 'diff_node_ch04_01_normal', 'diff_node_ch04_01_hard'])
            ],
            [
                'node_id' => 'node_ch04_02',
                'chapter_id' => 'chapter_04',
                'node_name' => '裂谷山道',
                'unlock_condition' => json_encode(['level' => 21, 'clear_node_id' => 'node_ch04_01', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch04_02_easy', 'diff_node_ch04_02_normal', 'diff_node_ch04_02_hard'])
            ],
            [
                'node_id' => 'node_ch04_03',
                'chapter_id' => 'chapter_04',
                'node_name' => '妖气古坛',
                'unlock_condition' => json_encode(['level' => 22, 'clear_node_id' => 'node_ch04_02', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch04_03_easy', 'diff_node_ch04_03_normal', 'diff_node_ch04_03_hard'])
            ],

            // 第五章节点
            [
                'node_id' => 'node_ch05_01',
                'chapter_id' => 'chapter_05',
                'node_name' => '深谷入口',
                'unlock_condition' => json_encode(['level' => 28, 'clear_node_id' => null, 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch05_01_easy', 'diff_node_ch05_01_normal', 'diff_node_ch05_01_hard'])
            ],
            [
                'node_id' => 'node_ch05_02',
                'chapter_id' => 'chapter_05',
                'node_name' => '幽泉裂隙',
                'unlock_condition' => json_encode(['level' => 29, 'clear_node_id' => 'node_ch05_01', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch05_02_easy', 'diff_node_ch05_02_normal', 'diff_node_ch05_02_hard'])
            ],
            [
                'node_id' => 'node_ch05_03',
                'chapter_id' => 'chapter_05',
                'node_name' => '迷踪谷底',
                'unlock_condition' => json_encode(['level' => 30, 'clear_node_id' => 'node_ch05_02', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch05_03_easy', 'diff_node_ch05_03_normal', 'diff_node_ch05_03_hard'])
            ],

            // 第六章节点
            [
                'node_id' => 'node_ch06_01',
                'chapter_id' => 'chapter_06',
                'node_name' => '登云古阶',
                'unlock_condition' => json_encode(['level' => 36, 'clear_node_id' => null, 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch06_01_easy', 'diff_node_ch06_01_normal', 'diff_node_ch06_01_hard'])
            ],
            [
                'node_id' => 'node_ch06_02',
                'chapter_id' => 'chapter_06',
                'node_name' => '云裂长桥',
                'unlock_condition' => json_encode(['level' => 37, 'clear_node_id' => 'node_ch06_01', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch06_02_easy', 'diff_node_ch06_02_normal', 'diff_node_ch06_02_hard'])
            ],
            [
                'node_id' => 'node_ch06_03',
                'chapter_id' => 'chapter_06',
                'node_name' => '雷鸣峰顶',
                'unlock_condition' => json_encode(['level' => 38, 'clear_node_id' => 'node_ch06_02', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch06_03_easy', 'diff_node_ch06_03_normal', 'diff_node_ch06_03_hard'])
            ],

            // 第七章节点
            [
                'node_id' => 'node_ch07_01',
                'chapter_id' => 'chapter_07',
                'node_name' => '百妖裂原',
                'unlock_condition' => json_encode(['level' => 44, 'clear_node_id' => null, 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch07_01_easy', 'diff_node_ch07_01_normal', 'diff_node_ch07_01_hard'])
            ],
            [
                'node_id' => 'node_ch07_02',
                'chapter_id' => 'chapter_07',
                'node_name' => '白骨兽径',
                'unlock_condition' => json_encode(['level' => 45, 'clear_node_id' => 'node_ch07_01', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch07_02_easy', 'diff_node_ch07_02_normal', 'diff_node_ch07_02_hard'])
            ],
            [
                'node_id' => 'node_ch07_03',
                'chapter_id' => 'chapter_07',
                'node_name' => '百妖战场',
                'unlock_condition' => json_encode(['level' => 46, 'clear_node_id' => 'node_ch07_02', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch07_03_easy', 'diff_node_ch07_03_normal', 'diff_node_ch07_03_hard'])
            ],

            // 第八章节点
            [
                'node_id' => 'node_ch08_01',
                'chapter_id' => 'chapter_08',
                'node_name' => '群山裂界',
                'unlock_condition' => json_encode(['level' => 52, 'clear_node_id' => null, 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch08_01_easy', 'diff_node_ch08_01_normal', 'diff_node_ch08_01_hard'])
            ],
            [
                'node_id' => 'node_ch08_02',
                'chapter_id' => 'chapter_08',
                'node_name' => '镇厄前线',
                'unlock_condition' => json_encode(['level' => 53, 'clear_node_id' => 'node_ch08_01', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch08_02_easy', 'diff_node_ch08_02_normal', 'diff_node_ch08_02_hard'])
            ],
            [
                'node_id' => 'node_ch08_03',
                'chapter_id' => 'chapter_08',
                'node_name' => '震山祭坛',
                'unlock_condition' => json_encode(['level' => 54, 'clear_node_id' => 'node_ch08_02', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_ch08_03_easy', 'diff_node_ch08_03_normal', 'diff_node_ch08_03_hard'])
            ],

            // 终章节点
            [
                'node_id' => 'node_epilogue_01',
                'chapter_id' => 'epilogue',
                'node_name' => '终局山门',
                'unlock_condition' => json_encode(['level' => 60, 'clear_node_id' => null, 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_epilogue_01_easy', 'diff_node_epilogue_01_normal', 'diff_node_epilogue_01_hard'])
            ],
            [
                'node_id' => 'node_epilogue_02',
                'chapter_id' => 'epilogue',
                'node_name' => '巡厄终阵',
                'unlock_condition' => json_encode(['level' => 60, 'clear_node_id' => 'node_epilogue_01', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_epilogue_02_easy', 'diff_node_epilogue_02_normal', 'diff_node_epilogue_02_hard'])
            ],
            [
                'node_id' => 'node_epilogue_03',
                'chapter_id' => 'epilogue',
                'node_name' => '山海尽头',
                'unlock_condition' => json_encode(['level' => 60, 'clear_node_id' => 'node_epilogue_02', 'conditions' => (object)[]]),
                'difficulty_ids' => json_encode(['diff_node_epilogue_03_easy', 'diff_node_epilogue_03_normal', 'diff_node_epilogue_03_hard'])
            ]
        ];

        // 插入数据
        DB::table('mainline_chapters')->insert($chapters);
        DB::table('mainline_nodes')->insert($nodes);

        $this->command->info('主线内容已导入：');
        $this->command->info('- 章节数：' . count($chapters));
        $this->command->info('- 节点数：' . count($nodes));
        $this->command->info('难度数据请运行 MainlineDifficultiesSeeder');
    }
}
