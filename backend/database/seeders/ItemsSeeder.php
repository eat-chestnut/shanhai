<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = collect();

        foreach ($this->baseItems() as $item) {
            $items->put($item['item_id'], $item);
        }

        foreach ($this->scriptureItems() as $item) {
            $items->put($item['item_id'], $item);
        }

        foreach ($this->equipmentItems() as $item) {
            $items->put($item['item_id'], $item);
        }

        foreach ($this->gemItems() as $item) {
            $items->put($item['item_id'], $item);
        }

        foreach ($this->blueAffixItems() as $item) {
            $items->put($item['item_id'], $item);
        }

        foreach ($this->purpleRefinementItems() as $item) {
            $items->put($item['item_id'], $item);
        }

        $legacyNames = $this->legacyNameMap();

        foreach ($this->collectReferencedItemIds() as $itemId) {
            if ($itemId === '' || $items->has($itemId)) {
                continue;
            }

            $items->put($itemId, $this->buildFallbackItem($itemId, $legacyNames[$itemId] ?? ''));
        }

        DB::table('items')->upsert(
            $items->values()->all(),
            ['item_id'],
            ['item_name', 'item_type', 'rarity', 'icon', 'desc', 'is_enabled'],
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function baseItems(): array
    {
        return [
            $this->item('gold', '金币', 'currency', 'common', 'icon_gold', '游戏中的通用货币。'),
            $this->item('jade', '灵玉', 'currency', 'rare', 'icon_jade', '用于高阶消耗与特殊购买的珍贵货币。'),
            $this->item('contribution', '贡献度', 'currency', 'uncommon', 'icon_contribution', '用于宗门商店和贡献玩法。'),
            $this->item('material_star_stone', '升星石', 'material', 'uncommon', 'icon_star_stone', '用于装备升星的基础材料。'),
            $this->item('material_star_crystal', '升星晶簇', 'material', 'rare', 'icon_star_crystal', '用于高阶装备升星的稀有材料。'),
            $this->item('material_seal_essence', '灵印精华', 'material', 'uncommon', 'icon_seal_essence', '用于符印与灵契成长的基础材料。'),
            $this->item('material_seal_crystal', '灵印晶髓', 'material', 'epic', 'icon_seal_crystal', '用于高阶符印与灵契成长。'),
            $this->item('material_refine_sand', '洗练砂', 'material', 'rare', 'icon_refine_sand', '用于装备洗练的常用材料。'),
            $this->item('material_refine_crystal', '淬灵晶尘', 'material', 'epic', 'icon_refine_crystal', '用于高阶洗练与精修。'),
            $this->item('boss_core_qingqiu', '青丘妖核', 'boss_material', 'epic', 'icon_qingqiu_core', '青丘首领掉落的核心材料。'),
            $this->item('boss_core_thunder', '雷鸣核心', 'boss_material', 'epic', 'icon_thunder_core', '雷系首领掉落的核心材料。'),
            $this->item('boss_core_abyss', '玄渊妖核', 'boss_material', 'epic', 'icon_abyss_core', '玄渊首领掉落的核心材料。'),
            $this->item('boss_core_test', '测试核心', 'boss_material', 'rare', 'icon_test_core', '测试掉落核心。'),
            $this->item('skill_book_thunder', '雷系技能书', 'consumable', 'rare', 'icon_thunder_book', '用于技能成长的雷系秘籍。'),
            $this->item('talisman_cloud', '青云护符', 'talisman', 'rare', 'icon_talisman_cloud', '可用于护符养成与词条展示。'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function scriptureItems(): array
    {
        $payload = $this->readJsonFile('scripture_items_and_drop_tags.json');

        return collect($payload['items'] ?? [])
            ->filter(static fn (mixed $entry): bool => is_array($entry))
            ->map(static fn (array $entry): array => [
                'item_id' => (string) ($entry['item_id'] ?? ''),
                'item_name' => (string) ($entry['item_name'] ?? ''),
                'item_type' => (string) ($entry['item_type'] ?? ''),
                'rarity' => (string) ($entry['rarity'] ?? 'common'),
                'icon' => (string) ($entry['icon'] ?? ''),
                'desc' => (string) ($entry['desc'] ?? ''),
                'is_enabled' => (bool) ($entry['is_enabled'] ?? true),
            ])
            ->filter(static fn (array $entry): bool => $entry['item_id'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function equipmentItems(): array
    {
        $payload = $this->readJsonFile('equipment_config.json');

        return collect($payload['equipment_config'] ?? [])
            ->filter(static fn (mixed $entry): bool => is_array($entry))
            ->map(fn (array $entry): array => $this->item(
                (string) ($entry['equip_id'] ?? ''),
                (string) ($entry['name'] ?? $entry['equip_id'] ?? ''),
                'equipment',
                $this->resolveEquipmentRarity((int) ($entry['level'] ?? 1), (string) ($entry['equip_id'] ?? '')),
                'icon_'.(string) ($entry['equip_id'] ?? ''),
                sprintf('装备部位：%s，等级 %d。', (string) ($entry['type'] ?? 'equipment'), (int) ($entry['level'] ?? 1)),
            ))
            ->filter(static fn (array $entry): bool => $entry['item_id'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function gemItems(): array
    {
        $payload = $this->readJsonFile('equipment_config.json');

        return collect($payload['gem_config'] ?? [])
            ->filter(static fn (mixed $entry): bool => is_array($entry))
            ->map(fn (array $entry): array => $this->item(
                (string) ($entry['gem_id'] ?? ''),
                (string) ($entry['name'] ?? $entry['gem_id'] ?? ''),
                'gem',
                $this->resolveGemRarity((string) ($entry['gem_id'] ?? '')),
                'icon_'.(string) ($entry['gem_id'] ?? ''),
                '镶嵌后可为角色提供额外属性。',
            ))
            ->filter(static fn (array $entry): bool => $entry['item_id'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function blueAffixItems(): array
    {
        $payload = $this->readJsonFile('equipment_config.json');

        return collect($payload['blue_affix_config'] ?? [])
            ->filter(static fn (mixed $entry): bool => is_array($entry))
            ->map(fn (array $entry): array => $this->item(
                (string) ($entry['affix_id'] ?? ''),
                (string) ($entry['name'] ?? $entry['affix_id'] ?? ''),
                'blue_affix',
                'rare',
                'icon_'.(string) ($entry['affix_id'] ?? ''),
                '用于装备蓝词条养成与萃取展示。',
            ))
            ->filter(static fn (array $entry): bool => $entry['item_id'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function purpleRefinementItems(): array
    {
        $payload = $this->readJsonFile('equipment_config.json');

        return collect($payload['purple_refinement_config'] ?? [])
            ->filter(static fn (mixed $entry): bool => is_array($entry))
            ->map(fn (array $entry): array => $this->item(
                (string) ($entry['refinement_id'] ?? ''),
                (string) ($entry['name'] ?? $entry['refinement_id'] ?? ''),
                'purple_refinement',
                'epic',
                'icon_'.(string) ($entry['refinement_id'] ?? ''),
                '用于装备紫炼化成长与展示。',
            ))
            ->filter(static fn (array $entry): bool => $entry['item_id'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function legacyNameMap(): array
    {
        $names = [];
        $bootstrap = $this->readRepoJson('data/bootstrap_state.json');

        foreach ($bootstrap['items'] ?? [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $itemId = (string) ($entry['item_id'] ?? '');
            $itemName = (string) ($entry['item_name'] ?? $entry['name'] ?? '');

            if ($itemId !== '' && $itemName !== '') {
                $names[$itemId] = $itemName;
            }
        }

        foreach ($this->readJsonFile('shop_item_config.json')['shop_item_config'] ?? [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $itemId = (string) ($entry['item_id'] ?? '');
            $itemName = (string) ($entry['item_name'] ?? '');

            if ($itemId !== '' && $itemName !== '') {
                $names[$itemId] = $itemName;
            }
        }

        return $names;
    }

    /**
     * @return list<string>
     */
    private function collectReferencedItemIds(): array
    {
        $itemIds = collect();

        foreach (config('game_runtime.reward_groups', []) as $group) {
            foreach ($group as $entry) {
                if (is_array($entry) && filled($entry['item_id'] ?? null)) {
                    $itemIds->push((string) $entry['item_id']);
                }
            }
        }

        foreach (config('game_runtime.starter_player.inventory', []) as $entry) {
            if (is_array($entry) && filled($entry['item_id'] ?? null)) {
                $itemIds->push((string) $entry['item_id']);
            }
        }

        $equipmentSummary = config('game_runtime.starter_player.equipment_summary', []);
        foreach ($equipmentSummary['equip_ids'] ?? [] as $itemId) {
            $itemIds->push((string) $itemId);
        }
        foreach ($equipmentSummary['equipped_boss_core_ids'] ?? [] as $itemId) {
            $itemIds->push((string) $itemId);
        }
        foreach ($equipmentSummary['equipped_gem_ids'] ?? [] as $itemId) {
            $itemIds->push((string) $itemId);
        }
        foreach ($equipmentSummary['blue_affix_ids'] ?? [] as $itemId) {
            $itemIds->push((string) $itemId);
        }
        foreach ($equipmentSummary['purple_refinement_ids'] ?? [] as $itemId) {
            $itemIds->push((string) $itemId);
        }
        foreach ($equipmentSummary['talisman_star_links'] ?? [] as $entry) {
            if (is_array($entry) && filled($entry['talisman_id'] ?? null)) {
                $itemIds->push((string) $entry['talisman_id']);
            }
        }

        foreach ([
            'dungeon_content_config.json',
            'task_config.json',
            'shop_item_config.json',
            'challenge_config.json',
            'scripture_upgrade_costs.json',
            'scripture_items_and_drop_tags.json',
        ] as $file) {
            $this->collectItemIdsFromValue($this->readJsonFile($file), $itemIds);
        }

        return $itemIds
            ->filter(static fn (mixed $itemId): bool => is_string($itemId) && $itemId !== '')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function collectItemIdsFromValue(mixed $value, \Illuminate\Support\Collection $itemIds): void
    {
        if (is_array($value)) {
            if (filled($value['item_id'] ?? null)) {
                $itemIds->push((string) $value['item_id']);
            }

            if (filled($value['cost_type'] ?? null) && in_array((string) $value['cost_type'], ['gold', 'jade', 'contribution'], true)) {
                $itemIds->push((string) $value['cost_type']);
            }

            foreach ($value as $nested) {
                $this->collectItemIdsFromValue($nested, $itemIds);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFallbackItem(string $itemId, string $legacyName = ''): array
    {
        $itemType = match (true) {
            Str::startsWith($itemId, 'equip_') => 'equipment',
            Str::startsWith($itemId, 'gem_') => 'gem',
            Str::startsWith($itemId, 'boss_core_') => 'boss_material',
            Str::startsWith($itemId, 'material_') => 'material',
            Str::startsWith($itemId, 'skill_book_') => 'consumable',
            Str::startsWith($itemId, 'blue_') => 'blue_affix',
            Str::startsWith($itemId, 'purple_') => 'purple_refinement',
            Str::startsWith($itemId, 'talisman_') => 'talisman',
            default => 'material',
        };

        return $this->item(
            $itemId,
            $legacyName !== '' ? $legacyName : $this->humanizeItemId($itemId),
            $itemType,
            $this->resolveFallbackRarity($itemId),
            'icon_'.$itemId,
            '根据现有玩法配置自动补齐的统一物品记录。',
        );
    }

    private function resolveEquipmentRarity(int $level, string $equipId): string
    {
        if (Str::contains($equipId, ['abyss', 'seal', 'tempest', 'warblade'])) {
            return 'epic';
        }

        return match (true) {
            $level >= 30 => 'rare',
            $level >= 10 => 'uncommon',
            default => 'common',
        };
    }

    private function resolveGemRarity(string $gemId): string
    {
        return match (true) {
            Str::contains($gemId, ['orange', 'purple']) => 'epic',
            Str::contains($gemId, ['blue', 'yellow']) => 'rare',
            default => 'uncommon',
        };
    }

    private function resolveFallbackRarity(string $itemId): string
    {
        return match (true) {
            Str::startsWith($itemId, 'boss_core_') => 'epic',
            Str::startsWith($itemId, 'purple_') => 'epic',
            Str::startsWith($itemId, 'blue_') => 'rare',
            Str::startsWith($itemId, 'gem_') => $this->resolveGemRarity($itemId),
            Str::startsWith($itemId, 'equip_') => $this->resolveEquipmentRarity((int) preg_replace('/\D+/', '', $itemId), $itemId),
            Str::startsWith($itemId, 'material_') => Str::contains($itemId, ['crystal']) ? 'epic' : 'uncommon',
            Str::startsWith($itemId, 'skill_book_') => 'rare',
            Str::startsWith($itemId, 'talisman_') => 'rare',
            default => 'common',
        };
    }

    private function humanizeItemId(string $itemId): string
    {
        return Str::of($itemId)
            ->replace('_', ' ')
            ->title()
            ->value();
    }

    /**
     * @return array<string, mixed>
     */
    private function item(
        string $itemId,
        string $itemName,
        string $itemType,
        string $rarity,
        string $icon,
        string $desc,
    ): array {
        return [
            'item_id' => $itemId,
            'item_name' => $itemName,
            'item_type' => $itemType,
            'rarity' => $rarity,
            'icon' => $icon,
            'desc' => $desc,
            'is_enabled' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function readJsonFile(string $filename): array
    {
        return $this->readRepoJson(database_path('seeders/data/'.$filename));
    }

    /**
     * @return array<string, mixed>
     */
    private function readRepoJson(string $path): array
    {
        $resolvedPath = $path;

        if (! is_file($resolvedPath)) {
            $candidatePath = base_path('../'.$path);

            if (! is_file($candidatePath)) {
                return [];
            }

            $resolvedPath = $candidatePath;
        }

        $decoded = json_decode((string) file_get_contents($resolvedPath), true);

        return is_array($decoded) ? $decoded : [];
    }
}
