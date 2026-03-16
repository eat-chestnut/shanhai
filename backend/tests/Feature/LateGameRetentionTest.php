<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LateGameRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_three_classes_can_enter_late_game_and_prepare_challenge(): void
    {
        $this->seed();

        foreach (['class_jingang', 'class_lingyu', 'class_fulu'] as $offset => $classId) {
            $playerId = 10001 + $offset;
            $token = $this->loginAndGetToken($playerId);
            $this->elevatePlayerToLateGame($playerId, $classId);

            $this->withHeader('Authorization', "Bearer {$token}")
                ->getJson('/api/v1/player/init')
                ->assertOk()
                ->assertJsonPath('data.player.class_id', $classId)
                ->assertJsonPath('data.player.build_summary.class_id', $classId);

            $prepare = $this->withHeader('Authorization', "Bearer {$token}")
                ->postJson('/api/v1/battle/prepare', [
                    'source_type' => 'challenge',
                    'source_id' => 'challenge_abyss_tower',
                    'difficulty_id' => 'floor_01',
                ]);

            $prepare
                ->assertOk()
                ->assertJsonPath('data.source_type', 'challenge')
                ->assertJsonPath('data.player_snapshot.class_id', $classId)
                ->assertJsonPath('data.player_snapshot.build_summary.class_id', $classId);

            $this->assertNotEmpty($prepare->json('data.player_snapshot.skills.active'));
        }
    }

    public function test_build_summary_reflects_late_game_sets_gems_and_affixes(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $this->elevatePlayerToLateGame(10001, 'class_fulu');
        $this->app['db']->table('player_profiles')
            ->where('player_id', 10001)
            ->update([
                'equipment_summary' => json_encode([
                    'equip_ids' => ['equip_abyss_blade', 'equip_abyss_robe', 'equip_spirit_crown_30', 'equip_wind_ring_30'],
                    'set_counts' => [
                        ['set_id' => 'set_abyss_60', 'equipped_count' => 4],
                    ],
                    'talisman_star_links' => [],
                    'equipped_boss_core_ids' => ['boss_core_abyss'],
                    'equipped_gem_ids' => ['boss_core_abyss', 'gem_orange'],
                    'blue_affix_ids' => ['blue_spell_focus'],
                    'purple_refinement_ids' => ['purple_refine_boss'],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/player/init')
            ->assertOk()
            ->assertJsonPath('data.player.build_summary.gem_tendency.focus', 'Boss爆发')
            ->assertJsonPath('data.player.build_summary.affix_direction.focus', '爆发')
            ->assertJsonPath('data.player.build_summary.primary_tendency', '爆发')
            ->assertJsonPath('data.player.build_summary.set_summary.0.set_id', 'set_abyss_60')
            ->assertJsonPath('data.player.growth_recommendations.0', '当前主方向为爆发，建议优先挑战周常塔层并补足高阶成长材料。');
    }

    public function test_late_game_star_up_requires_advanced_material(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $this->elevatePlayerToLateGame(10001, 'class_jingang');
        $equipmentUid = $this->promoteWeaponToLateGameTier($token, 'equip_abyss_blade', 5);

        $this->app['db']->table('player_items')
            ->where('player_id', 10001)
            ->where('item_id', 'material_star_crystal')
            ->update(['count' => 0]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/equipment/star_up', [
                'equipment_uid' => $equipmentUid,
            ])
            ->assertStatus(400)
            ->assertJsonPath('code', 40061)
            ->assertJsonPath('msg', '高阶材料不足');
    }

    public function test_idle_status_returns_late_game_payload_with_cap(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $this->elevatePlayerToLateGame(10001, 'class_jingang');
        $this->app['db']->table('player_profiles')
            ->where('player_id', 10001)
            ->update([
                'idle_last_claimed_at' => now()->subHours(20),
                'last_active_at' => now()->subHours(20),
            ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/idle/status')
            ->assertOk()
            ->assertJsonPath('data.rule.rule_id', 'idle_lategame')
            ->assertJsonPath('data.claimable_seconds', 43200)
            ->assertJsonPath('data.is_capped', true);
    }

    public function test_idle_claim_grants_rewards_and_repeat_claim_is_blocked(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $this->elevatePlayerToLateGame(10001, 'class_jingang');
        $this->app['db']->table('player_profiles')
            ->where('player_id', 10001)
            ->update([
                'idle_last_claimed_at' => now()->subHours(2),
                'last_active_at' => now()->subHours(2),
            ]);

        $firstClaim = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/idle/claim');

        $firstClaim
            ->assertOk();

        $this->assertLessThanOrEqual(1, (float) $firstClaim->json('data.status.claimable_seconds', 0));

        $this->assertDatabaseHas('player_items', [
            'player_id' => 10001,
            'item_id' => 'material_star_crystal',
            'count' => 4,
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/idle/claim')
            ->assertStatus(400)
            ->assertJsonPath('code', 40095)
            ->assertJsonPath('msg', '暂无可领取收益');
    }

    public function test_challenge_list_and_detail_return_late_game_runtime_payload(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $this->elevatePlayerToLateGame(10001, 'class_jingang');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/challenge/list')
            ->assertOk()
            ->assertJsonPath('data.challenges.0.challenge_id', 'challenge_abyss_tower');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/challenge/detail?challenge_id=challenge_abyss_tower')
            ->assertOk()
            ->assertJsonPath('data.challenge.challenge_id', 'challenge_abyss_tower')
            ->assertJsonPath('data.challenge.floors.0.floor_id', 'floor_01')
            ->assertJsonPath('data.challenge.floors.0.monster_ids.0', 'mon_abyss_guard');
    }

    public function test_challenge_first_clear_only_once_and_floor_progresses(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $this->elevatePlayerToLateGame(10001, 'class_lingyu');

        $battleId = $this->prepareChallengeBattle($token, 'floor_01');
        $firstSettle = $this->settleBattle($token, $battleId);

        $firstSettle
            ->assertOk()
            ->assertJsonPath('data.progress_update.challenge_id', 'challenge_abyss_tower')
            ->assertJsonPath('data.progress_update.is_first_clear_now', true)
            ->assertJsonPath('data.progress_update.highest_floor', 1)
            ->assertJsonFragment([
                'item_id' => 'material_star_crystal',
            ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/challenge/detail?challenge_id=challenge_abyss_tower')
            ->assertOk()
            ->assertJsonPath('data.challenge.current_floor', 2);

        $secondBattleId = $this->prepareChallengeBattle($token, 'floor_01');
        $this->settleBattle($token, $secondBattleId)
            ->assertOk()
            ->assertJsonPath('data.first_clear_rewards', [])
            ->assertJsonPath('data.weekly_rewards', [])
            ->assertJsonPath('data.progress_update.is_first_clear_now', false);
    }

    public function test_challenge_weekly_rewards_reset_on_new_week(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $this->elevatePlayerToLateGame(10001, 'class_fulu');

        $battleId = $this->prepareChallengeBattle($token, 'floor_01');
        $this->settleBattle($token, $battleId)->assertOk();

        $this->app['db']->table('player_challenge_progress')
            ->where('player_id', 10001)
            ->where('challenge_id', 'challenge_abyss_tower')
            ->update([
                'week_key' => Carbon::now()->subWeek()->startOfWeek(Carbon::MONDAY)->toDateString(),
                'weekly_reward_claimed_floors' => json_encode(['floor_01'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'weekly_highest_floor' => 1,
                'weekly_clear_count' => 1,
            ]);

        $nextBattleId = $this->prepareChallengeBattle($token, 'floor_01');
        $response = $this->settleBattle($token, $nextBattleId);

        $response
            ->assertOk()
            ->assertJsonPath('data.first_clear_rewards', [])
            ->assertJsonFragment([
                'item_id' => 'material_star_crystal',
            ])
            ->assertJsonPath('data.progress_update.weekly_highest_floor', 1);
    }

    public function test_late_game_task_and_shop_content_are_available(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $this->elevatePlayerToLateGame(10001, 'class_jingang');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/task/list')
            ->assertOk()
            ->assertJsonFragment([
                'task_id' => 'task_mainline_level60',
            ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/shop/common/list')
            ->assertOk()
            ->assertJsonFragment([
                'shop_item_id' => 'common_refine_crystal',
            ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/shop/sect/list')
            ->assertOk()
            ->assertJsonFragment([
                'shop_item_id' => 'sect_boss_core_abyss',
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

    private function elevatePlayerToLateGame(int $playerId, string $classId): void
    {
        $this->app['db']->table('player_profiles')
            ->where('player_id', $playerId)
            ->update([
                'class_id' => $classId,
                'level' => 65,
                'power' => 12600,
                'current_chapter_id' => 'chapter_05',
                'current_node_id' => 'node_08',
                'gold' => 12000,
                'contribution' => 3200,
                'updated_at' => now(),
            ]);
    }

    private function promoteWeaponToLateGameTier(string $token, string $equipId, int $starLevel): string
    {
        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/equipment/detail')
            ->assertOk();

        $equipmentRow = $this->app['db']->table('player_equipments')
            ->where('player_id', 10001)
            ->where('slot_type', 'weapon')
            ->first();

        $equipmentUid = (string) ($equipmentRow->equipment_uid ?? '');

        $this->app['db']->table('player_equipments')
            ->where('equipment_uid', $equipmentUid)
            ->update([
                'equip_id' => $equipId,
                'slot_type' => 'weapon',
                'star_level' => $starLevel,
                'updated_at' => now(),
            ]);

        return $equipmentUid;
    }

    private function prepareChallengeBattle(string $token, string $floorId): string
    {
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/battle/prepare', [
                'source_type' => 'challenge',
                'source_id' => 'challenge_abyss_tower',
                'difficulty_id' => $floorId,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 0);

        return (string) $response->json('data.battle_id');
    }

    private function settleBattle(string $token, string $battleId)
    {
        return $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/battle/settle', [
                'battle_id' => $battleId,
                'result' => 'victory',
                'duration' => 31.5,
                'cleared_wave' => 1,
                'client_summary' => [
                    'defeated_monsters' => ['mon_abyss_guard', 'mon_abyss_lord'],
                ],
            ]);
    }
}
