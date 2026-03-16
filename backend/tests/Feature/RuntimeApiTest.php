<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuntimeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_init_succeeds(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/player/init');

        $response
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('msg', 'ok')
            ->assertJsonPath('data.player.player_id', 10001)
            ->assertJsonPath('data.player.class_id', 'class_jingang')
            ->assertJsonPath('data.inventory.0.item_id', 'blue_atk_flat');
    }

    public function test_class_selection_persists(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/class/select', [
                'class_id' => 'class_lingyu',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.player.class_id', 'class_lingyu');

        $this->assertDatabaseHas('player_profiles', [
            'player_id' => 10001,
            'class_id' => 'class_lingyu',
        ]);
    }

    public function test_stage_chapter_list_returns_runtime_payload(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/stage/chapter/list');

        $response
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.chapters.0.chapter_id', 'chapter_01')
            ->assertJsonPath('data.chapters.0.nodes.0.node_id', 'node_01')
            ->assertJsonPath('data.chapters.0.nodes.0.is_unlocked', true);
    }

    public function test_battle_prepare_succeeds(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/battle/prepare', [
                'source_type' => 'stage',
                'source_id' => 'node_01',
                'difficulty_id' => 'easy',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.source_type', 'stage')
            ->assertJsonPath('data.source_id', 'node_01')
            ->assertJsonPath('data.difficulty_id', 'easy')
            ->assertJsonPath('data.player_snapshot.class_id', 'class_jingang')
            ->assertJsonPath('data.enemy_group_snapshot.monsters.0.monster_id', 'mon_qingqiu_guard');
    }

    public function test_battle_settle_grants_normal_rewards(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $battleId = $this->prepareBattle($token);

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/battle/settle', [
                'battle_id' => $battleId,
                'result' => 'victory',
                'duration' => 12.6,
                'cleared_wave' => 1,
                'client_summary' => [
                    'defeated_monsters' => ['mon_qingqiu_guard', 'mon_qingqiu_boss'],
                ],
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonFragment([
                'item_id' => 'gem_blue',
                'count' => 1,
            ]);

        $this->assertDatabaseHas('player_items', [
            'player_id' => 10001,
            'item_id' => 'gem_blue',
            'count' => 1,
        ]);
    }

    public function test_first_clear_rewards_are_only_granted_once(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $firstBattleId = $this->prepareBattle($token);
        $firstResponse = $this->settleBattle($token, $firstBattleId);
        $firstResponse
            ->assertOk()
            ->assertJsonFragment([
                'item_id' => 'gold',
                'count' => 100,
            ]);

        $secondBattleId = $this->prepareBattle($token);
        $secondResponse = $this->settleBattle($token, $secondBattleId);
        $secondResponse
            ->assertOk()
            ->assertJsonPath('data.first_clear_rewards', []);

        $this->assertDatabaseHas('player_stage_progress', [
            'player_id' => 10001,
            'node_id' => 'node_01',
            'difficulty_id' => 'easy',
            'is_first_clear' => true,
            'clear_count' => 2,
        ]);
    }

    public function test_duplicate_battle_id_does_not_grant_rewards_twice(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $battleId = $this->prepareBattle($token);

        $this->settleBattle($token, $battleId)->assertOk();

        $duplicateResponse = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/battle/settle', [
                'battle_id' => $battleId,
                'result' => 'victory',
                'duration' => 6.0,
                'cleared_wave' => 1,
                'client_summary' => [],
            ]);

        $duplicateResponse
            ->assertStatus(409)
            ->assertJsonPath('code', 40941)
            ->assertJsonPath('msg', 'battle_id 已结算');

        $this->assertDatabaseHas('player_items', [
            'player_id' => 10001,
            'item_id' => 'gem_blue',
            'count' => 1,
        ]);
    }

    public function test_inventory_list_returns_persisted_items(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/inventory/list');

        $response
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.items.0.item_id', 'blue_atk_flat')
            ->assertJsonPath('data.currencies.gold', 5000);
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

    private function prepareBattle(string $token): string
    {
        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/battle/prepare', [
                'source_type' => 'stage',
                'source_id' => 'node_01',
                'difficulty_id' => 'easy',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 0);

        return (string) $response->json('data.battle_id');
    }

    private function settleBattle(string $token, string $battleId)
    {
        return $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/battle/settle', [
                'battle_id' => $battleId,
                'result' => 'victory',
                'duration' => 8.5,
                'cleared_wave' => 1,
                'client_summary' => [
                    'defeated_monsters' => ['mon_qingqiu_guard', 'mon_qingqiu_boss'],
                ],
            ]);
    }
}
