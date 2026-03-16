<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MainlineDifficultiesSeeder extends Seeder
{
    public function run(): void
    {
        $difficulties = [
            // 序章难度
            ['difficulty_id' => 'diff_node_prologue_01_easy', 'node_id' => 'node_prologue_01', 'recommended_power' => 80, 'first_clear_reward_group_id' => 'reward_diff_node_prologue_01_easy'],
            ['difficulty_id' => 'diff_node_prologue_01_normal', 'node_id' => 'node_prologue_01', 'recommended_power' => 120, 'first_clear_reward_group_id' => 'reward_diff_node_prologue_01_normal'],
            ['difficulty_id' => 'diff_node_prologue_01_hard', 'node_id' => 'node_prologue_01', 'recommended_power' => 180, 'first_clear_reward_group_id' => 'reward_diff_node_prologue_01_hard'],

            ['difficulty_id' => 'diff_node_prologue_02_easy', 'node_id' => 'node_prologue_02', 'recommended_power' => 100, 'first_clear_reward_group_id' => 'reward_diff_node_prologue_02_easy'],
            ['difficulty_id' => 'diff_node_prologue_02_normal', 'node_id' => 'node_prologue_02', 'recommended_power' => 150, 'first_clear_reward_group_id' => 'reward_diff_node_prologue_02_normal'],
            ['difficulty_id' => 'diff_node_prologue_02_hard', 'node_id' => 'node_prologue_02', 'recommended_power' => 220, 'first_clear_reward_group_id' => 'reward_diff_node_prologue_02_hard'],

            ['difficulty_id' => 'diff_node_prologue_03_easy', 'node_id' => 'node_prologue_03', 'recommended_power' => 120, 'first_clear_reward_group_id' => 'reward_diff_node_prologue_03_easy'],
            ['difficulty_id' => 'diff_node_prologue_03_normal', 'node_id' => 'node_prologue_03', 'recommended_power' => 180, 'first_clear_reward_group_id' => 'reward_diff_node_prologue_03_normal'],
            ['difficulty_id' => 'diff_node_prologue_03_hard', 'node_id' => 'node_prologue_03', 'recommended_power' => 260, 'first_clear_reward_group_id' => 'reward_diff_node_prologue_03_hard'],

            // 第一章难度
            ['difficulty_id' => 'diff_node_ch01_01_easy', 'node_id' => 'node_ch01_01', 'recommended_power' => 180, 'first_clear_reward_group_id' => 'reward_diff_node_ch01_01_easy'],
            ['difficulty_id' => 'diff_node_ch01_01_normal', 'node_id' => 'node_ch01_01', 'recommended_power' => 260, 'first_clear_reward_group_id' => 'reward_diff_node_ch01_01_normal'],
            ['difficulty_id' => 'diff_node_ch01_01_hard', 'node_id' => 'node_ch01_01', 'recommended_power' => 360, 'first_clear_reward_group_id' => 'reward_diff_node_ch01_01_hard'],

            ['difficulty_id' => 'diff_node_ch01_02_easy', 'node_id' => 'node_ch01_02', 'recommended_power' => 220, 'first_clear_reward_group_id' => 'reward_diff_node_ch01_02_easy'],
            ['difficulty_id' => 'diff_node_ch01_02_normal', 'node_id' => 'node_ch01_02', 'recommended_power' => 320, 'first_clear_reward_group_id' => 'reward_diff_node_ch01_02_normal'],
            ['difficulty_id' => 'diff_node_ch01_02_hard', 'node_id' => 'node_ch01_02', 'recommended_power' => 440, 'first_clear_reward_group_id' => 'reward_diff_node_ch01_02_hard'],

            ['difficulty_id' => 'diff_node_ch01_03_easy', 'node_id' => 'node_ch01_03', 'recommended_power' => 260, 'first_clear_reward_group_id' => 'reward_diff_node_ch01_03_easy'],
            ['difficulty_id' => 'diff_node_ch01_03_normal', 'node_id' => 'node_ch01_03', 'recommended_power' => 380, 'first_clear_reward_group_id' => 'reward_diff_node_ch01_03_normal'],
            ['difficulty_id' => 'diff_node_ch01_03_hard', 'node_id' => 'node_ch01_03', 'recommended_power' => 520, 'first_clear_reward_group_id' => 'reward_diff_node_ch01_03_hard'],

            // 第二章难度
            ['difficulty_id' => 'diff_node_ch02_01_easy', 'node_id' => 'node_ch02_01', 'recommended_power' => 340, 'first_clear_reward_group_id' => 'reward_diff_node_ch02_01_easy'],
            ['difficulty_id' => 'diff_node_ch02_01_normal', 'node_id' => 'node_ch02_01', 'recommended_power' => 480, 'first_clear_reward_group_id' => 'reward_diff_node_ch02_01_normal'],
            ['difficulty_id' => 'diff_node_ch02_01_hard', 'node_id' => 'node_ch02_01', 'recommended_power' => 650, 'first_clear_reward_group_id' => 'reward_diff_node_ch02_01_hard'],

            ['difficulty_id' => 'diff_node_ch02_02_easy', 'node_id' => 'node_ch02_02', 'recommended_power' => 380, 'first_clear_reward_group_id' => 'reward_diff_node_ch02_02_easy'],
            ['difficulty_id' => 'diff_node_ch02_02_normal', 'node_id' => 'node_ch02_02', 'recommended_power' => 540, 'first_clear_reward_group_id' => 'reward_diff_node_ch02_02_normal'],
            ['difficulty_id' => 'diff_node_ch02_02_hard', 'node_id' => 'node_ch02_02', 'recommended_power' => 720, 'first_clear_reward_group_id' => 'reward_diff_node_ch02_02_hard'],

            ['difficulty_id' => 'diff_node_ch02_03_easy', 'node_id' => 'node_ch02_03', 'recommended_power' => 420, 'first_clear_reward_group_id' => 'reward_diff_node_ch02_03_easy'],
            ['difficulty_id' => 'diff_node_ch02_03_normal', 'node_id' => 'node_ch02_03', 'recommended_power' => 600, 'first_clear_reward_group_id' => 'reward_diff_node_ch02_03_normal'],
            ['difficulty_id' => 'diff_node_ch02_03_hard', 'node_id' => 'node_ch02_03', 'recommended_power' => 800, 'first_clear_reward_group_id' => 'reward_diff_node_ch02_03_hard'],

            // 终章难度（示例）
            ['difficulty_id' => 'diff_node_epilogue_01_easy', 'node_id' => 'node_epilogue_01', 'recommended_power' => 2860, 'first_clear_reward_group_id' => 'reward_diff_node_epilogue_01_easy'],
            ['difficulty_id' => 'diff_node_epilogue_01_normal', 'node_id' => 'node_epilogue_01', 'recommended_power' => 3460, 'first_clear_reward_group_id' => 'reward_diff_node_epilogue_01_normal'],
            ['difficulty_id' => 'diff_node_epilogue_01_hard', 'node_id' => 'node_epilogue_01', 'recommended_power' => 4160, 'first_clear_reward_group_id' => 'reward_diff_node_epilogue_01_hard'],
        ];

        DB::table('mainline_difficulties')->insert($difficulties);
        $this->command->info('主线难度数据已导入：' . count($difficulties) . ' 条记录');
    }
}
