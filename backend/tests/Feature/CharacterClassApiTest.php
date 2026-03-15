<?php

namespace Tests\Feature;

use Database\Seeders\CharacterClassSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterClassApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_paginated_character_classes(): void
    {
        $this->seed(CharacterClassSeeder::class);

        $response = $this->getJson('/api/v1/character-classes?search=class_jingang');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.class_id', 'class_jingang')
            ->assertJsonPath('data.0.class_name', '金刚');
    }

    public function test_it_can_create_a_character_class_via_api(): void
    {
        $payload = [
            'class_id' => 'class_yingren',
            'class_name' => '影刃',
            'class_desc' => '爆发型近战职业',
            'role_type' => 'melee',
            'is_open' => true,
        ];

        $response = $this->postJson('/api/v1/character-classes', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('data.class_id', 'class_yingren')
            ->assertJsonPath('data.class_name', '影刃')
            ->assertJsonPath('data.is_open', true);

        $this->assertDatabaseHas('character_classes', [
            'class_id' => 'class_yingren',
            'class_name' => '影刃',
        ]);
    }
}
