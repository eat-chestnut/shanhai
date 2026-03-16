<?php

namespace Database\Seeders;

use App\Models\MainlineDifficulty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MainlineDifficultiesSeeder extends Seeder
{
    public function run(): void
    {
        $payload = json_decode((string) file_get_contents(database_path('seeders/data/mainline_config.json')), true);
        $difficulties = collect($payload['difficulty_config'] ?? [])
            ->filter(static fn (mixed $entry): bool => is_array($entry))
            ->map(static function (array $entry): array {
                $difficultyId = (string) ($entry['difficulty_id'] ?? '');

                return [
                    'difficulty_id' => $difficultyId,
                    'node_id' => (string) ($entry['node_id'] ?? ''),
                    'difficulty_order' => (int) ($entry['difficulty_order'] ?? MainlineDifficulty::defaultDifficultyOrder($difficultyId)),
                    'difficulty_name' => (string) ($entry['difficulty_name'] ?? MainlineDifficulty::defaultDifficultyName($difficultyId)),
                    'recommended_power' => max((int) ($entry['recommended_power'] ?? 0), 0),
                    'first_clear_reward_group_id' => (string) ($entry['first_clear_reward_group_id'] ?? ''),
                ];
            })
            ->filter(static fn (array $entry): bool => $entry['difficulty_id'] !== '' && $entry['node_id'] !== '')
            ->values()
            ->all();

        DB::table('mainline_difficulties')->insert($difficulties);
        $this->command->info('主线难度数据已导入：' . count($difficulties) . ' 条记录');
    }
}
