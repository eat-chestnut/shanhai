<?php

namespace Tests\Feature;

use Database\Seeders\EquipmentConfigSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_equipment_config_for_client(): void
    {
        $this->seed(EquipmentConfigSeeder::class);

        $response = $this->getJson('/api/v1/equipment-config');

        $response
            ->assertOk()
            ->assertJsonFragment(['equip_id' => 'equip_bow_01', 'name' => '长弓'])
            ->assertJsonFragment(['set_id' => 'set_zhaoyao_20'])
            ->assertJsonFragment(['gem_id' => 'gem_purple', 'name' => '紫宝石'])
            ->assertJsonFragment(['affix_id' => 'blue_atk_flat'])
            ->assertJsonFragment(['refinement_id' => 'purple_refine_boss']);
    }

    public function test_it_imports_equipment_config_from_json_command(): void
    {
        $this->artisan('game:import-equipment-config', [
            'path' => database_path('seeders/data/equipment_config.json'),
        ])->assertExitCode(0);

        $this->assertDatabaseCount('equipment', 4);
        $this->assertDatabaseCount('equipment_sets', 2);
        $this->assertDatabaseCount('gems', 4);
        $this->assertDatabaseCount('blue_affixes', 2);
        $this->assertDatabaseCount('purple_refinements', 2);
        $this->assertDatabaseHas('gems', [
            'gem_id' => 'gem_purple',
            'bonus_boss_dmg' => 15,
        ]);
    }

    public function test_it_exports_equipment_config_to_json_file(): void
    {
        $this->seed(EquipmentConfigSeeder::class);

        $path = storage_path('app/testing/equipment_config_export.json');

        if (is_file($path)) {
            unlink($path);
        }

        $this->artisan('game:export-equipment-config', [
            'path' => $path,
        ])->assertExitCode(0);

        $this->assertFileExists($path);

        $payload = json_decode((string) file_get_contents($path), true);

        $this->assertIsArray($payload);
        $this->assertContains('equip_staff_01', array_column($payload['equipment_config'], 'equip_id'));
        $this->assertContains('set_warrior_40', array_column($payload['equipment_set_config'], 'set_id'));
        $this->assertContains('blue_atk_flat', array_column($payload['blue_affix_config'], 'affix_id'));
        $this->assertContains('purple_refine_boss', array_column($payload['purple_refinement_config'], 'refinement_id'));
    }
}
