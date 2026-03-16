<?php

namespace Tests\Feature;

use Database\Seeders\CharacterClassSeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_paginated_skills(): void
    {
        $this->seed([
            CharacterClassSeeder::class,
            SkillSeeder::class,
        ]);

        $response = $this->getJson('/api/v1/skills?search=skill_lingyu_arrow');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.skill_id', 'skill_lingyu_arrow')
            ->assertJsonPath('data.0.class_id', 'class_lingyu')
            ->assertJsonPath('data.0.effect_type', 'damage')
            ->assertJsonPath('data.0.range', 'multi');
    }

    public function test_it_can_create_a_skill_via_api(): void
    {
        $this->seed(CharacterClassSeeder::class);

        $payload = [
            'skill_id' => 'skill_fulu_thunder',
            'class_id' => 'class_fulu',
            'skill_name' => '雷箓镇煞',
            'skill_desc' => '召引雷符轰击目标并施加感电。',
            'type' => 'active',
            'effect_type' => 'damage',
            'target_type' => 'single',
            'cooldown' => 4,
            'cost' => 28,
            'unlock_level' => 8,
            'max_level' => 5,
            'power_base' => 160,
            'power_per_level' => 25,
            'duration' => 3,
            'chance' => 0,
            'stat_bonuses' => [],
            'effect_payload' => [
                'status_name' => '感电',
                'status_type' => 'dot',
                'status_duration' => 3,
                'status_tick_interval' => 1,
                'status_power_ratio' => 0.2,
            ],
            'is_open' => true,
        ];

        $response = $this->postJson('/api/v1/skills', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('data.skill_id', 'skill_fulu_thunder')
            ->assertJsonPath('data.class_id', 'class_fulu')
            ->assertJsonPath('data.effect_payload.status_type', 'dot');

        $this->assertDatabaseHas('skills', [
            'skill_id' => 'skill_fulu_thunder',
            'class_id' => 'class_fulu',
            'skill_name' => '雷箓镇煞',
        ]);
    }
}
