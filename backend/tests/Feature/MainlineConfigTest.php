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
            ->assertJsonPath('chapter_config.0.chapter_id', 'prologue')
            ->assertJsonPath('chapter_config.0.chapter_name', '序章：山海初醒')
            ->assertJsonPath('node_config.0.node_id', 'prologue_node_01')
            ->assertJsonPath('node_config.1.difficulty_ids.1', 'normal')
            ->assertJsonPath('difficulty_config.0.first_clear_reward_group_id', 'reward_prologue01_easy')
            ->assertJsonPath('difficulty_config.0.difficulty_order', 0)
            ->assertJsonPath('difficulty_config.0.difficulty_name', '简单');
    }

    public function test_it_imports_mainline_config_from_json_command(): void
    {
        $this->artisan('game:import-mainline-config', [
            'path' => database_path('seeders/data/mainline_config.json'),
        ])->assertExitCode(0);

        $this->assertDatabaseCount('mainline_chapters', 10);
        $this->assertDatabaseCount('mainline_nodes', 28);
        $this->assertDatabaseCount('mainline_difficulties', 60);
        $this->assertDatabaseHas('mainline_chapters', [
            'chapter_id' => 'chapter_02',
            'required_previous_highest_difficulty' => 'nightmare',
        ]);
        $this->assertDatabaseHas('mainline_difficulties', [
            'difficulty_id' => 'nightmare',
            'node_id' => 'node_24',
            'difficulty_order' => 0,
            'difficulty_name' => '梦魇',
            'first_clear_reward_group_id' => 'reward_node24_nightmare',
        ]);
        $this->assertDatabaseHas('mainline_difficulties', [
            'difficulty_id' => 'normal',
            'node_id' => 'prologue_node_02',
            'difficulty_order' => 1,
            'difficulty_name' => '普通',
            'first_clear_reward_group_id' => 'reward_prologue02_normal',
        ]);
    }

    public function test_exported_mainline_config_contains_required_unlock_and_difficulty_fields(): void
    {
        $this->seed(MainlineConfigSeeder::class);

        $path = storage_path('app/testing/mainline_config_export.json');

        if (is_file($path)) {
            unlink($path);
        }

        $this->artisan('game:export-mainline-config', [
            'path' => $path,
        ])->assertExitCode(0);

        $payload = json_decode((string) file_get_contents($path), true);

        $this->assertSame('prologue', $payload['chapter_config'][0]['chapter_id']);
        $this->assertArrayHasKey('required_previous_chapter', $payload['chapter_config'][1]);
        $this->assertArrayHasKey('required_previous_highest_difficulty', $payload['chapter_config'][1]);
        $this->assertArrayHasKey('difficulty_order', $payload['difficulty_config'][0]);
        $this->assertArrayHasKey('difficulty_name', $payload['difficulty_config'][0]);
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
        $this->assertSame('prologue', $payload['chapter_config'][0]['chapter_id']);
        $this->assertSame('prologue_node_01', $payload['difficulty_config'][0]['node_id']);
        $this->assertSame(0, $payload['difficulty_config'][0]['difficulty_order']);
        $this->assertSame('简单', $payload['difficulty_config'][0]['difficulty_name']);
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
            'difficulty_id' => 'hard',
            'node_id' => 'node_test',
            'difficulty_order' => 3,
            'difficulty_name' => '困难',
            'recommended_power' => 300,
            'first_clear_reward_group_id' => 'reward_hard',
        ]);

        MainlineDifficulty::query()->create([
            'difficulty_id' => 'easy',
            'node_id' => 'node_test',
            'difficulty_order' => 1,
            'difficulty_name' => '简单',
            'recommended_power' => 100,
            'first_clear_reward_group_id' => 'reward_easy',
        ]);

        $this->assertSame(['easy', 'hard'], $node->refresh()->difficulty_ids);
    }
}
