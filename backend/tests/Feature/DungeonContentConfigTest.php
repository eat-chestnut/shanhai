<?php

namespace Tests\Feature;

use App\Enums\MonsterDropKind;
use App\Models\MonsterDrop;
use Database\Seeders\DungeonContentConfigSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DungeonContentConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_dungeon_content_config_for_client(): void
    {
        $this->seed(DungeonContentConfigSeeder::class);

        $response = $this->getJson('/api/v1/dungeon-content-config');

        $response
            ->assertOk()
            ->assertJsonPath('dungeon_config.0.dungeon_id', 'dungeon_gem')
            ->assertJsonFragment([
                'difficulty_id' => 'nightmare',
                'dungeon_id' => 'dungeon_refine',
                'first_clear_reward_group_id' => 'reward_dungeon_refine_nightmare',
            ])
            ->assertJsonFragment([
                'monster_id' => 'mon_new_boss',
                'name' => '雷鸣灵兽',
                'base_hp' => 3500,
                'base_atk' => 180,
                'is_boss' => true,
            ])
            ->assertJsonFragment([
                'monster_id' => 'mon_new_boss',
                'item_id' => 'boss_core_thunder',
                'drop_kind' => 'boss_core',
            ]);
    }

    public function test_it_imports_dungeon_content_config_from_json_command(): void
    {
        $this->artisan('game:import-dungeon-content-config', [
            'path' => database_path('seeders/data/dungeon_content_config.json'),
        ])->assertExitCode(0);

        $this->assertDatabaseCount('dungeons', 4);
        $this->assertDatabaseCount('dungeon_difficulties', 13);
        $this->assertDatabaseCount('monsters', 12);
        $this->assertDatabaseCount('monster_drops', 21);
        $this->assertDatabaseHas('monster_drops', [
            'monster_id' => 'mon_new_boss',
            'item_id' => 'boss_core_thunder',
            'drop_kind' => MonsterDropKind::BossCore->value,
        ]);
        $this->assertDatabaseHas('dungeon_difficulties', [
            'dungeon_id' => 'dungeon_new',
            'difficulty_id' => 'hard',
            'first_clear_reward_group_id' => 'reward_dungeon_new_hard',
        ]);
    }

    public function test_it_exports_dungeon_content_config_to_json_file(): void
    {
        $this->seed(DungeonContentConfigSeeder::class);

        $path = storage_path('app/testing/dungeon_content_config_export.json');

        if (is_file($path)) {
            unlink($path);
        }

        $this->artisan('game:export-dungeon-content-config', [
            'path' => $path,
        ])->assertExitCode(0);

        $this->assertFileExists($path);

        $payload = json_decode((string) file_get_contents($path), true);

        $this->assertIsArray($payload);
        $this->assertSame('dungeon_gem', $payload['dungeon_config'][0]['dungeon_id']);
        $this->assertSame(13, count($payload['dungeon_difficulty_config']));
        $this->assertContains(
            'reward_dungeon_new_hard',
            array_column($payload['dungeon_difficulty_config'], 'first_clear_reward_group_id'),
        );
        $this->assertContains('boss_fixed', array_column($payload['monster_drop_config'], 'drop_kind'));
        $this->assertContains('mon_abyss_lord', array_column($payload['monster_config'], 'monster_id'));
    }

    public function test_it_infers_boss_drop_kinds_when_json_omits_them(): void
    {
        $payload = [
            'dungeon_config' => [
                [
                    'dungeon_id' => 'dungeon_test',
                    'dungeon_name' => '测试副本',
                    'unlock_level' => 1,
                ],
            ],
            'dungeon_difficulty_config' => [
                [
                    'difficulty_id' => 'easy',
                    'dungeon_id' => 'dungeon_test',
                    'recommended_power' => 100,
                ],
            ],
            'monster_config' => [
                [
                    'monster_id' => 'mon_test_boss',
                    'name' => '测试 Boss',
                    'base_hp' => 2000,
                    'base_atk' => 200,
                    'is_boss' => true,
                ],
            ],
            'monster_drop_config' => [
                [
                    'monster_id' => 'mon_test_boss',
                    'item_id' => 'gem_gold',
                    'drop_rate' => 1.0,
                ],
                [
                    'monster_id' => 'mon_test_boss',
                    'item_id' => 'boss_core_test',
                    'drop_rate' => 0.05,
                ],
            ],
        ];

        file_put_contents(
            database_path('seeders/data/dungeon_content_config_test.json'),
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL,
        );

        $this->artisan('game:import-dungeon-content-config', [
            'path' => database_path('seeders/data/dungeon_content_config_test.json'),
        ])->assertExitCode(0);

        $this->assertSame(
            MonsterDropKind::BossFixed->value,
            MonsterDrop::query()->where('item_id', 'gem_gold')->value('drop_kind'),
        );
        $this->assertSame(
            MonsterDropKind::BossCore->value,
            MonsterDrop::query()->where('item_id', 'boss_core_test')->value('drop_kind'),
        );
    }
}
