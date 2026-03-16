<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SustainableLoopRuntimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_equipment_equip_succeeds(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $equipment = $this->findEquipmentByTemplate($token, 'equip_bow_01');

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/equipment/equip', [
                'equipment_uid' => $equipment['equipment_uid'],
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.selected_equipment.equip_id', 'equip_bow_01')
            ->assertJsonPath('data.selected_equipment.is_equipped', true)
            ->assertJsonPath('data.equipped_slots.weapon.equip_id', 'equip_bow_01');
    }

    public function test_equipment_unequip_succeeds(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $equipment = $this->findEquipmentByTemplate($token, 'equip_bow_01');

        $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/equipment/equip', [
                'equipment_uid' => $equipment['equipment_uid'],
            ])
            ->assertOk();

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/equipment/unequip', [
                'equipment_uid' => $equipment['equipment_uid'],
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.selected_equipment.is_equipped', false);
    }

    public function test_equipment_star_up_fails_when_materials_are_insufficient(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $equipment = $this->findEquipmentByTemplate($token, 'equip_sword_01');

        $this->assertDatabaseHas('player_items', [
            'player_id' => 10001,
            'item_id' => 'material_star_stone',
            'count' => 8,
        ]);

        $this->assertDatabaseHas('player_items', [
            'player_id' => 10001,
            'item_id' => 'material_star_stone',
            'count' => 8,
        ]);

        $this->app['db']->table('player_items')
            ->where('player_id', 10001)
            ->where('item_id', 'material_star_stone')
            ->update(['count' => 0]);

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/equipment/star_up', [
                'equipment_uid' => $equipment['equipment_uid'],
            ]);

        $response
            ->assertStatus(400)
            ->assertJsonPath('code', 40061)
            ->assertJsonPath('msg', '材料不足');
    }

    public function test_equipment_socket_gem_succeeds(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $equipment = $this->findEquipmentByTemplate($token, 'equip_bow_01');

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/equipment/socket_gem', [
                'equipment_uid' => $equipment['equipment_uid'],
                'gem_id' => 'gem_green',
                'slot_index' => 0,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.selected_equipment.gem_slots.0.gem_id', 'gem_green');
    }

    public function test_equipment_extract_blue_affix_succeeds(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $equipment = $this->findEquipmentByTemplate($token, 'equip_bow_01');

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/equipment/extract_blue_affix', [
                'equipment_uid' => $equipment['equipment_uid'],
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 0);

        $this->assertNotEmpty((string) $response->json('data.selected_equipment.blue_affix.affix_id'));
    }

    public function test_equipment_refine_purple_affix_succeeds(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $equipment = $this->findEquipmentByTemplate($token, 'equip_bow_01');

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/equipment/refine_purple_affix', [
                'equipment_uid' => $equipment['equipment_uid'],
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 0);

        $this->assertNotEmpty((string) $response->json('data.selected_equipment.purple_refinement.refinement_id'));
    }

    public function test_dungeon_list_returns_runtime_payload(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/dungeon/list');

        $response
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.dungeons.0.dungeon_id', 'dungeon_gem')
            ->assertJsonPath('data.dungeons.0.daily_limit', 3);
    }

    public function test_dungeon_detail_returns_remaining_times(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/dungeon/detail?dungeon_id=dungeon_gem');

        $response
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.dungeon.dungeon_id', 'dungeon_gem')
            ->assertJsonPath('data.dungeon.remaining_count', 3);
    }

    public function test_dungeon_settle_is_blocked_when_daily_count_is_exhausted(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $battleId = $this->prepareDungeonBattle($token, 'dungeon_gem', 'easy');

        $this->app['db']->table('player_dungeon_progress')->insert([
            'player_id' => 10001,
            'dungeon_id' => 'dungeon_gem',
            'difficulty_id' => 'easy',
            'is_first_clear' => false,
            'clear_count' => 0,
            'daily_count' => 3,
            'daily_reset_at' => now()->startOfDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/battle/settle', [
                'battle_id' => $battleId,
                'result' => 'victory',
                'duration' => 12.4,
                'cleared_wave' => 1,
                'client_summary' => [],
            ]);

        $response
            ->assertStatus(400)
            ->assertJsonPath('code', 40053)
            ->assertJsonPath('msg', '副本次数不足');
    }

    public function test_dungeon_first_clear_reward_is_only_granted_once(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $firstBattleId = $this->prepareDungeonBattle($token, 'dungeon_gem', 'easy');
        $firstResponse = $this->settleBattle($token, $firstBattleId);
        $firstResponse
            ->assertOk()
            ->assertJsonPath('data.progress_update.source_type', 'dungeon')
            ->assertJsonPath('data.progress_update.is_first_clear_now', true);

        $secondBattleId = $this->prepareDungeonBattle($token, 'dungeon_gem', 'easy');
        $secondResponse = $this->settleBattle($token, $secondBattleId);
        $secondResponse
            ->assertOk()
            ->assertJsonPath('data.first_clear_rewards', [])
            ->assertJsonPath('data.progress_update.is_first_clear_now', false);
    }

    public function test_task_list_returns_runtime_payload(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/task/list');

        $response
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.tasks.0.task_id', 'task_daily_battle_3');
    }

    public function test_task_claim_succeeds(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/task/claim', [
                'task_id' => 'task_mainline_level20',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonFragment([
                'task_id' => 'task_mainline_level20',
                'is_claimed' => true,
            ]);
    }

    public function test_task_claim_cannot_repeat(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/task/claim', [
                'task_id' => 'task_mainline_level20',
            ])
            ->assertOk();

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/task/claim', [
                'task_id' => 'task_mainline_level20',
            ]);

        $response
            ->assertStatus(400)
            ->assertJsonPath('code', 40071)
            ->assertJsonPath('msg', '任务不可领取');
    }

    public function test_task_claim_all_succeeds(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $this->app['db']->table('player_stage_progress')->updateOrInsert(
            [
                'player_id' => 10001,
                'node_id' => 'node_01',
                'difficulty_id' => 'easy',
            ],
            [
                'chapter_id' => 'chapter_01',
                'is_first_clear' => true,
                'clear_count' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/task/claim_all');

        $response
            ->assertOk()
            ->assertJsonPath('code', 0);

        $this->assertGreaterThanOrEqual(2, count($response->json('data.claimed_task_ids', [])));
    }

    public function test_common_shop_buy_succeeds(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/shop/common/buy', [
                'shop_item_id' => 'common_star_stone',
                'count' => 1,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.player.gold', 4760);

        $this->assertDatabaseHas('player_items', [
            'player_id' => 10001,
            'item_id' => 'material_star_stone',
            'count' => 10,
        ]);
    }

    public function test_sect_shop_buy_succeeds(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/shop/sect/buy', [
                'shop_item_id' => 'sect_seal_essence',
                'count' => 1,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.player.contribution', 1050);

        $this->assertDatabaseHas('player_items', [
            'player_id' => 10001,
            'item_id' => 'material_seal_essence',
            'count' => 6,
        ]);
    }

    public function test_shop_buy_fails_when_currency_is_insufficient(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $this->app['db']->table('player_profiles')
            ->where('player_id', 10001)
            ->update(['gold' => 0]);

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/shop/common/buy', [
                'shop_item_id' => 'common_star_stone',
                'count' => 1,
            ]);

        $response
            ->assertStatus(400)
            ->assertJsonPath('code', 40091)
            ->assertJsonPath('msg', '货币不足');
    }

    public function test_shop_buy_fails_when_buy_limit_is_exceeded(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/shop/common/buy', [
                'shop_item_id' => 'common_star_stone',
                'count' => 3,
            ])
            ->assertOk();

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/shop/common/buy', [
                'shop_item_id' => 'common_star_stone',
                'count' => 1,
            ]);

        $response
            ->assertStatus(400)
            ->assertJsonPath('code', 40081)
            ->assertJsonPath('msg', '商品已售罄');
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

    /**
     * @return array<string, mixed>
     */
    private function findEquipmentByTemplate(string $token, string $equipId): array
    {
        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/equipment/detail');

        $response
            ->assertOk()
            ->assertJsonPath('code', 0);

        return collect($response->json('data.equipment_list', []))
            ->firstWhere('equip_id', $equipId);
    }

    private function prepareDungeonBattle(string $token, string $dungeonId, string $difficultyId): string
    {
        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/battle/prepare', [
                'source_type' => 'dungeon',
                'source_id' => $dungeonId,
                'difficulty_id' => $difficultyId,
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
