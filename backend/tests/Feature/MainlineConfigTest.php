<?php

namespace Tests\Feature;

use App\Models\MainlineChapter;
use App\Models\MainlineDifficulty;
use App\Models\MainlineNode;
use Database\Seeders\MainlineConfigSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MainlineConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_mainline_config_for_client(): void
    {
        $this->seed(MainlineConfigSeeder::class);

        $response = $this->getJson('/api/v1/mainline-config');

        $response
            ->assertOk()
            ->assertJsonPath('chapter_config.0.chapter_id', 'chapter_01')
            ->assertJsonPath('chapter_config.0.chapter_name', '奉命巡山')
            ->assertJsonPath('node_config.0.node_id', 'node_01')
            ->assertJsonPath('node_config.0.difficulty_ids.2', 'hard')
            ->assertJsonPath('difficulty_config.0.first_clear_reward_group_id', 'reward_node01_easy');
    }

    public function test_it_imports_mainline_config_from_json_command(): void
    {
        $this->artisan('game:import-mainline-config', [
            'path' => database_path('seeders/data/mainline_config.json'),
        ])->assertExitCode(0);

        $this->assertDatabaseCount('mainline_chapters', 1);
        $this->assertDatabaseCount('mainline_nodes', 1);
        $this->assertDatabaseCount('mainline_difficulties', 3);
        $this->assertDatabaseHas('mainline_difficulties', [
            'difficulty_id' => 'hard',
            'node_id' => 'node_01',
            'first_clear_reward_group_id' => 'reward_node01_hard',
        ]);
    }

    public function test_it_exports_mainline_config_to_json_file(): void
    {
        $this->seed(MainlineConfigSeeder::class);

        $path = storage_path('app/testing/mainline_config_export.json');

        if (is_file($path)) {
            unlink($path);
        }

        $this->artisan('game:export-mainline-config', [
            'path' => $path,
        ])->assertExitCode(0);

        $this->assertFileExists($path);

        $payload = json_decode((string) file_get_contents($path), true);

        $this->assertIsArray($payload);
        $this->assertSame('chapter_01', $payload['chapter_config'][0]['chapter_id']);
        $this->assertSame('node_01', $payload['difficulty_config'][0]['node_id']);
    }

    public function test_it_syncs_node_difficulty_ids_when_difficulties_change(): void
    {
        MainlineChapter::query()->create([
            'chapter_id' => 'chapter_test',
            'chapter_name' => '测试章节',
            'unlock_level' => 1,
        ]);

        $node = MainlineNode::query()->create([
            'node_id' => 'node_test',
            'chapter_id' => 'chapter_test',
            'node_name' => '测试节点',
            'unlock_condition' => ['level' => 1],
            'difficulty_ids' => [],
        ]);

        MainlineDifficulty::query()->create([
            'difficulty_id' => 'easy',
            'node_id' => 'node_test',
            'recommended_power' => 100,
            'first_clear_reward_group_id' => 'reward_easy',
        ]);

        MainlineDifficulty::query()->create([
            'difficulty_id' => 'hard',
            'node_id' => 'node_test',
            'recommended_power' => 300,
            'first_clear_reward_group_id' => 'reward_hard',
        ]);

        $this->assertSame(['easy', 'hard'], $node->refresh()->difficulty_ids);
    }
}
