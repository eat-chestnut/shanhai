<?php

namespace Tests\Feature;

use App\Models\PlayerItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScriptureRuntimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scripture_list_and_detail_follow_formal_unlock_condition(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();

        $lockedList = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/scripture/list');

        $lockedList
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.scriptures.0.scripture_id', 'nanshan_1')
            ->assertJsonPath('data.scriptures.0.is_unlocked', false)
            ->assertJsonPath('data.scriptures.0.current_world_level', 0)
            ->assertJsonPath('data.scriptures.0.max_unlocked_world_level', 0);

        $this->unlockScripture();

        $unlockedList = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/scripture/list');

        $unlockedList
            ->assertOk()
            ->assertJsonPath('data.scriptures.0.scripture_id', 'nanshan_1')
            ->assertJsonPath('data.scriptures.0.scripture_name', '南山经')
            ->assertJsonPath('data.scriptures.0.scripture_group', '五藏山经')
            ->assertJsonPath('data.scriptures.0.is_unlocked', true)
            ->assertJsonPath('data.scriptures.0.current_world_level', 1)
            ->assertJsonPath('data.scriptures.0.max_unlocked_world_level', 1);

        $detail = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/scripture/detail?scripture_id=nanshan_1');

        $detail
            ->assertOk()
            ->assertJsonPath('data.scripture_id', 'nanshan_1')
            ->assertJsonPath('data.scripture_name', '南山经')
            ->assertJsonPath('data.current_world_level', 1)
            ->assertJsonPath('data.max_unlocked_world_level', 1)
            ->assertJsonPath('data.available_world_levels.0', 1)
            ->assertJsonPath('data.tier_preview.0.world_level_start', 1)
            ->assertJsonPath('data.tier_preview.0.world_level_end', 9)
            ->assertJsonPath('data.tier_preview.0.normal_monster_ids.0', 'monster_wolf')
            ->assertJsonPath('data.tier_preview.0.extra_drop_tags.0', 'low_bone')
            ->assertJsonPath('data.upgrade_cost_preview.0.target_world_level', 2);
    }

    public function test_scripture_upgrade_only_allows_defined_target_world_levels(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $this->unlockScripture();
        $this->grantItem('nanshan_bone', 20);
        $this->grantItem('mountain_seal_fragment', 5);

        $undefinedUpgrade = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/scripture/upgrade', [
                'scripture_id' => 'nanshan_1',
                'target_world_level' => 3,
            ]);

        $undefinedUpgrade
            ->assertStatus(400)
            ->assertJsonPath('code', 4001)
            ->assertJsonPath('msg', '目标等级未配置升级成本');

        $successUpgrade = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/scripture/upgrade', [
                'scripture_id' => 'nanshan_1',
                'target_world_level' => 2,
            ]);

        $successUpgrade
            ->assertOk()
            ->assertJsonPath('data.scripture_id', 'nanshan_1')
            ->assertJsonPath('data.previous_world_level', 1)
            ->assertJsonPath('data.current_world_level', 2)
            ->assertJsonPath('data.inventory_update.0.item_id', 'nanshan_bone')
            ->assertJsonPath('data.currencies_update.gold_coin', -500);

        $this->assertDatabaseHas('player_scripture_progress', [
            'player_id' => 10001,
            'scripture_id' => 'nanshan_1',
            'current_world_level' => 2,
            'max_unlocked_world_level' => 2,
        ]);

        $this->assertSame(10, (int) PlayerItem::query()->where('player_id', 10001)->where('item_id', 'nanshan_bone')->value('count'));
        $this->assertSame(3, (int) PlayerItem::query()->where('player_id', 10001)->where('item_id', 'mountain_seal_fragment')->value('count'));
        $this->assertDatabaseHas('player_profiles', [
            'player_id' => 10001,
            'gold' => 4500,
        ]);
    }

    public function test_scripture_battle_prepare_and_settle_use_formal_tier_pool_and_rewards(): void
    {
        $this->seed();

        $token = $this->loginAndGetToken();
        $this->unlockScripture();

        $prepare = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/battle/prepare', [
                'source_type' => 'scripture',
                'source_id' => 'nanshan_1',
                'world_level' => 1,
            ]);

        $prepare
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.source_type', 'scripture')
            ->assertJsonPath('data.source_id', 'nanshan_1')
            ->assertJsonPath('data.world_level', 1)
            ->assertJsonPath('data.enemy_group_snapshot.normal_monster_ids.0', 'monster_wolf')
            ->assertJsonPath('data.enemy_group_snapshot.elite_monster_ids.0', 'monster_wolf_elite')
            ->assertJsonPath('data.enemy_group_snapshot.boss_monster_ids.0', 'monster_mountain_king')
            ->assertJsonPath('data.battle_rules_snapshot.hp_scale', 1)
            ->assertJsonPath('data.battle_rules_snapshot.extra_drop_tags.1', 'low_wood');

        $battleId = (string) $prepare->json('data.battle_id');

        $this->assertDatabaseHas('battle_records', [
            'battle_id' => $battleId,
            'source_type' => 'scripture',
            'source_id' => 'nanshan_1',
            'world_level' => 1,
        ]);

        $settle = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/battle/settle', [
                'battle_id' => $battleId,
                'result' => 'victory',
                'duration' => 11.8,
                'cleared_wave' => 1,
                'client_summary' => [
                    'defeated_monsters' => ['monster_wolf', 'monster_tree_spirit', 'monster_mountain_king'],
                ],
            ]);

        $settle
            ->assertOk()
            ->assertJsonPath('data.progress_update.source_type', 'scripture')
            ->assertJsonPath('data.progress_update.scripture_id', 'nanshan_1')
            ->assertJsonPath('data.progress_update.world_level', 1)
            ->assertJsonFragment(['item_id' => 'nanshan_bone'])
            ->assertJsonFragment(['item_id' => 'mountain_seal_fragment']);

        $this->assertGreaterThan(
            0,
            (int) PlayerItem::query()->where('player_id', 10001)->where('item_id', 'nanshan_bone')->value('count')
        );
        $this->assertGreaterThan(
            0,
            (int) PlayerItem::query()->where('player_id', 10001)->where('item_id', 'mountain_seal_fragment')->value('count')
        );
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

    private function unlockScripture(): void
    {
        $this->app['db']->table('player_stage_progress')->updateOrInsert(
            [
                'player_id' => 10001,
                'node_id' => 'epilogue_node_02',
                'difficulty_id' => 'easy',
            ],
            [
                'chapter_id' => 'epilogue',
                'is_first_clear' => true,
                'clear_count' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    private function grantItem(string $itemId, int $count): void
    {
        $this->app['db']->table('player_items')->updateOrInsert(
            [
                'player_id' => 10001,
                'item_id' => $itemId,
            ],
            [
                'count' => $count,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }
}
