<?php

namespace Tests\Feature;

use Database\Seeders\HallFeatureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HallFeatureApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_paginated_hall_features(): void
    {
        $this->seed(HallFeatureSeeder::class);

        $response = $this->getJson('/api/v1/hall-features?search=feat_trial');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.feature_id', 'feat_trial')
            ->assertJsonPath('data.0.feature_name', '宗门试炼')
            ->assertJsonPath('data.0.unlock_condition.level', 1)
            ->assertJsonPath('data.0.jump_target.page', 'trial');
    }

    public function test_it_can_create_a_hall_feature_via_api(): void
    {
        $payload = [
            'feature_id' => 'feat_arena',
            'feature_name' => '竞技场',
            'feature_type' => 'pvp',
            'unlock_condition' => [
                'level' => 12,
                'conditions' => [
                    'chapter' => '2-5',
                ],
            ],
            'jump_target' => [
                'page' => 'arena',
                'params' => [
                    'tab' => 'rank',
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/hall-features', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('data.feature_id', 'feat_arena')
            ->assertJsonPath('data.unlock_condition.level', 12)
            ->assertJsonPath('data.jump_target.page', 'arena');

        $this->assertDatabaseHas('hall_features', [
            'feature_id' => 'feat_arena',
            'feature_name' => '竞技场',
            'feature_type' => 'pvp',
        ]);
    }
}
