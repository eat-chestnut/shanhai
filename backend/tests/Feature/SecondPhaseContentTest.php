<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecondPhaseContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_lingyu_can_be_selected_in_runtime(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/class/select', [
                'class_id' => 'class_lingyu',
            ])
            ->assertOk()
            ->assertJsonPath('data.player.class_id', 'class_lingyu');
    }

    public function test_fulu_can_be_selected_in_runtime(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/class/select', [
                'class_id' => 'class_fulu',
            ])
            ->assertOk()
            ->assertJsonPath('data.player.class_id', 'class_fulu');
    }

    public function test_three_classes_can_init_prepare_and_settle(): void
    {
        $this->seed();

        foreach (['class_jingang', 'class_lingyu', 'class_fulu'] as $classId) {
            $token = $this->loginAndGetToken();

            $this->withHeader('Authorization', "Bearer {$token}")
                ->postJson('/api/v1/class/select', [
                    'class_id' => $classId,
                ])
                ->assertOk()
                ->assertJsonPath('data.player.class_id', $classId);

            $prepare = $this->withHeader('Authorization', "Bearer {$token}")
                ->postJson('/api/v1/battle/prepare', [
                    'source_type' => 'stage',
                    'source_id' => 'node_01',
                    'difficulty_id' => 'easy',
                ]);

            $prepare
                ->assertOk()
                ->assertJsonPath('data.player_snapshot.class_id', $classId);

            $battleId = (string) $prepare->json('data.battle_id');

            $this->withHeader('Authorization', "Bearer {$token}")
                ->postJson('/api/v1/battle/settle', [
                    'battle_id' => $battleId,
                    'result' => 'victory',
                    'duration' => 15.2,
                    'cleared_wave' => 1,
                    'client_summary' => [],
                ])
                ->assertOk()
                ->assertJsonPath('data.result', 'victory');
        }
    }

    public function test_midline_chapters_can_be_read(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/stage/chapter/list')
            ->assertOk()
            ->assertJsonFragment([
                'chapter_id' => 'chapter_03',
                'chapter_name' => '第三章：符火照夜',
            ]);
    }

    public function test_midline_node_returns_progress_state(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/stage/node/detail?node_id=node_04')
            ->assertOk()
            ->assertJsonPath('data.node.node_id', 'node_04')
            ->assertJsonPath('data.node.progress_state', 'current');
    }

    public function test_mid_dungeon_detail_returns_enriched_payload(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $this->unlockDungeonNewForPlayer();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/dungeon/detail?dungeon_id=dungeon_new')
            ->assertOk()
            ->assertJsonPath('data.dungeon.dungeon_id', 'dungeon_new')
            ->assertJsonPath('data.dungeon.is_unlocked', true)
            ->assertJsonPath('data.dungeon.current_tier', 'hard');
    }

    public function test_mid_dungeon_reward_and_count_progress_are_correct(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $this->unlockDungeonNewForPlayer();

        $prepare = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/battle/prepare', [
                'source_type' => 'dungeon',
                'source_id' => 'dungeon_new',
                'difficulty_id' => 'hard',
            ]);

        $prepare->assertOk();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/battle/settle', [
                'battle_id' => (string) $prepare->json('data.battle_id'),
                'result' => 'victory',
                'duration' => 22.4,
                'cleared_wave' => 1,
                'client_summary' => [],
            ])
            ->assertOk()
            ->assertJsonPath('data.progress_update.dungeon_id', 'dungeon_new')
            ->assertJsonPath('data.progress_update.daily_count', 1);
    }

    public function test_prepare_returns_midgame_skills_for_lingyu(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/class/select', [
                'class_id' => 'class_lingyu',
            ])
            ->assertOk();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/battle/prepare', [
                'source_type' => 'stage',
                'source_id' => 'node_01',
                'difficulty_id' => 'easy',
            ])
            ->assertOk()
            ->assertJsonFragment([
                'skill_id' => 'skill_lingyu_stormshot',
            ]);
    }

    public function test_boss_profile_patterns_are_returned_for_mid_boss(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $this->unlockDungeonNewForPlayer();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/battle/prepare', [
                'source_type' => 'dungeon',
                'source_id' => 'dungeon_new',
                'difficulty_id' => 'hard',
            ])
            ->assertOk()
            ->assertJsonFragment([
                'monster_id' => 'mon_new_boss',
            ])
            ->assertJsonFragment([
                'pattern_type' => 'summon',
            ]);
    }

    public function test_mid_equipment_config_contains_new_entries(): void
    {
        $this->seed();

        $this->getJson('/api/v1/equipment-config')
            ->assertOk()
            ->assertJsonFragment([
                'equip_id' => 'equip_warblade_30',
            ])
            ->assertJsonFragment([
                'set_id' => 'set_lingfeng_40',
            ])
            ->assertJsonFragment([
                'gem_id' => 'gem_orange',
            ]);
    }

    public function test_mid_task_and_shop_content_are_available(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/task/list')
            ->assertOk()
            ->assertJsonFragment([
                'task_id' => 'task_mainline_level40',
            ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/shop/common/list')
            ->assertOk()
            ->assertJsonFragment([
                'shop_item_id' => 'common_gem_orange',
            ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/shop/sect/list')
            ->assertOk()
            ->assertJsonFragment([
                'shop_item_id' => 'sect_skill_book_thunder',
            ]);
    }

    private function loginAndGetToken(int $playerId = 10001): string
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'player_id' => $playerId,
            'nickname' => "巡厄弟子 {$playerId}",
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 0);

        return (string) $response->json('data.token');
    }

    private function unlockDungeonNewForPlayer(): void
    {
        $this->app['db']->table('player_stage_progress')->updateOrInsert(
            [
                'player_id' => 10001,
                'node_id' => 'node_06',
                'difficulty_id' => 'hard',
            ],
            [
                'chapter_id' => 'chapter_02',
                'is_first_clear' => true,
                'clear_count' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }
}
