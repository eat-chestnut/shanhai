<?php

namespace Database\Seeders;

use App\Models\MainlineChapter;
use App\Models\Scripture;
use App\Models\ScriptureChapterBinding;
use App\Models\ScriptureDropTag;
use App\Models\ScriptureMonster;
use App\Models\ScriptureUpgradeCost;
use App\Models\ScriptureWorldTier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use JsonException;

class ScriptureDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedScriptures();
            $this->seedBindings();
            $this->seedWorldTiers();
            $this->seedUpgradeCosts();
            $this->seedDropTags();
            $this->seedMonsters();
        });
    }

    private function seedScriptures(): void
    {
        Scripture::query()->delete();

        foreach ($this->readJsonFile('scriptures.json')['scriptures'] ?? [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            Scripture::query()->create([
                'scripture_id' => (string) ($entry['scripture_id'] ?? ''),
                'scripture_name' => (string) ($entry['scripture_name'] ?? ''),
                'scripture_group' => (string) ($entry['scripture_group'] ?? ''),
                'sort_order' => (int) ($entry['sort_order'] ?? 0),
                'unlock_condition' => $entry['unlock_condition'] ?? [],
                'is_enabled' => (bool) ($entry['is_enabled'] ?? true),
            ]);
        }
    }

    private function seedBindings(): void
    {
        ScriptureChapterBinding::query()->delete();
        MainlineChapter::query()->update(['scripture_id' => null]);

        foreach ($this->readJsonFile('scripture_chapter_bindings.json')['bindings'] ?? [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $scriptureId = (string) ($entry['scripture_id'] ?? '');
            $chapterId = (string) ($entry['chapter_id'] ?? '');

            ScriptureChapterBinding::query()->create([
                'scripture_id' => $scriptureId,
                'chapter_id' => $chapterId,
                'sort_order' => (int) ($entry['sort_order'] ?? 0),
            ]);

            MainlineChapter::query()
                ->where('chapter_id', $chapterId)
                ->update([
                    'scripture_id' => $scriptureId,
                ]);
        }
    }

    private function seedWorldTiers(): void
    {
        ScriptureWorldTier::query()->delete();

        foreach ($this->readJsonFile('scripture_world_tiers.json')['tiers'] ?? [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            ScriptureWorldTier::query()->create([
                'scripture_id' => (string) ($entry['scripture_id'] ?? ''),
                'world_level_start' => (int) ($entry['world_level_start'] ?? 0),
                'world_level_end' => (int) ($entry['world_level_end'] ?? 0),
                'hp_scale' => (float) ($entry['hp_scale'] ?? 1),
                'atk_scale' => (float) ($entry['atk_scale'] ?? 1),
                'def_scale' => (float) ($entry['def_scale'] ?? 1),
                'reward_scale' => (float) ($entry['reward_scale'] ?? 1),
                'gold_scale' => (float) ($entry['gold_scale'] ?? 1),
                'normal_monster_ids' => array_values($entry['normal_monster_ids'] ?? []),
                'elite_monster_ids' => array_values($entry['elite_monster_ids'] ?? []),
                'boss_monster_ids' => array_values($entry['boss_monster_ids'] ?? []),
                'extra_drop_tags' => array_values($entry['extra_drop_tags'] ?? []),
                'new_feature_note' => (string) ($entry['new_feature_note'] ?? ''),
            ]);
        }
    }

    private function seedUpgradeCosts(): void
    {
        ScriptureUpgradeCost::query()->delete();

        foreach ($this->readJsonFile('scripture_upgrade_costs.json')['upgrade_costs'] ?? [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            ScriptureUpgradeCost::query()->create([
                'scripture_id' => (string) ($entry['scripture_id'] ?? ''),
                'target_world_level' => (int) ($entry['target_world_level'] ?? 0),
                'cost_items' => array_values($entry['cost_items'] ?? []),
                'cost_gold' => (int) ($entry['cost_gold'] ?? 0),
                'required_player_level' => (int) ($entry['required_player_level'] ?? 1),
            ]);
        }
    }

    private function seedDropTags(): void
    {
        ScriptureDropTag::query()->delete();

        foreach ($this->readJsonFile('scripture_items_and_drop_tags.json')['drop_tags'] ?? [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            ScriptureDropTag::query()->create([
                'drop_tag' => (string) ($entry['drop_tag'] ?? ''),
                'tag_name' => (string) ($entry['tag_name'] ?? ''),
                'items' => array_values($entry['items'] ?? []),
            ]);
        }
    }

    private function seedMonsters(): void
    {
        ScriptureMonster::query()->delete();

        foreach ($this->readJsonFile('scripture_monsters.json')['monsters'] ?? [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            ScriptureMonster::query()->create([
                'monster_id' => (string) ($entry['monster_id'] ?? ''),
                'name' => (string) ($entry['name'] ?? ''),
                'monster_type' => (string) ($entry['monster_type'] ?? ''),
                'race' => (string) ($entry['race'] ?? ''),
                'rarity' => (string) ($entry['rarity'] ?? ''),
                'base_hp' => (int) ($entry['base_hp'] ?? 0),
                'base_atk' => (int) ($entry['base_atk'] ?? 0),
                'base_def' => (int) ($entry['base_def'] ?? 0),
                'move_speed' => (int) ($entry['move_speed'] ?? 0),
                'ai_type' => (string) ($entry['ai_type'] ?? ''),
                'skill_ids' => array_values($entry['skill_ids'] ?? []),
                'is_boss' => (bool) ($entry['is_boss'] ?? false),
                'is_elite' => (bool) ($entry['is_elite'] ?? false),
                'is_enabled' => (bool) ($entry['is_enabled'] ?? true),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function readJsonFile(string $filename): array
    {
        $path = database_path("seeders/data/{$filename}");

        if (! is_file($path)) {
            throw new InvalidArgumentException("Scripture JSON file not found: {$path}");
        }

        try {
            $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException("Scripture JSON file is invalid: {$filename}", previous: $exception);
        }

        return is_array($payload) ? $payload : [];
    }
}
