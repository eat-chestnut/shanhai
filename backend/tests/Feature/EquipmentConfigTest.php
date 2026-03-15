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
            ->assertJsonPath('equipment_config.0.equip_id', 'equip_armor_01')
            ->assertJsonPath('equipment_set_config.0.set_id', 'set_zhaoyao_20')
            ->assertJsonPath('gem_config.1.gem_id', 'gem_green')
            ->assertJsonPath('blue_affix_config.0.affix_id', 'blue_atk_flat')
            ->assertJsonPath('purple_refinement_config.0.refinement_id', 'purple_refine_boss');
    }

    public function test_it_imports_equipment_config_from_json_command(): void
    {
        $this->artisan('game:import-equipment-config', [
            'path' => database_path('seeders/data/equipment_config.json'),
        ])->assertExitCode(0);

        $this->assertDatabaseCount('equipment', 2);
        $this->assertDatabaseCount('equipment_sets', 1);
        $this->assertDatabaseCount('gems', 2);
        $this->assertDatabaseCount('blue_affixes', 1);
        $this->assertDatabaseCount('purple_refinements', 1);
        $this->assertDatabaseHas('gems', [
            'gem_id' => 'gem_blue',
            'bonus_boss_dmg' => 10,
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
        $this->assertSame('equip_armor_01', $payload['equipment_config'][0]['equip_id']);
        $this->assertSame('set_zhaoyao_20', $payload['equipment_set_config'][0]['set_id']);
        $this->assertSame('blue_atk_flat', $payload['blue_affix_config'][0]['affix_id']);
        $this->assertSame('purple_refine_boss', $payload['purple_refinement_config'][0]['refinement_id']);
    }
}
