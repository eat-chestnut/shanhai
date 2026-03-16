<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemConfigsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // 货币
            ['item_id' => 'gold_coin', 'item_name' => '金币', 'item_type' => 'currency', 'rarity' => 'common', 'icon' => 'coin_gold', 'description' => '游戏基础货币'],
            ['item_id' => 'diamond', 'item_name' => '钻石', 'item_type' => 'currency', 'rarity' => 'legendary', 'icon' => 'gem_diamond', 'description' => '高级货币'],
            ['item_id' => 'soul_stone', 'item_name' => '魂石', 'item_type' => 'currency', 'rarity' => 'epic', 'icon' => 'stone_soul', 'description' => '特殊货币'],
            
            // 基础材料
            ['item_id' => 'iron_ore', 'item_name' => '铁矿', 'item_type' => 'material', 'rarity' => 'common', 'icon' => 'ore_iron', 'description' => '基础锻造材料'],
            ['item_id' => 'wood', 'item_name' => '木材', 'item_type' => 'material', 'rarity' => 'common', 'icon' => 'wood', 'description' => '基础建造材料'],
            ['item_id' => 'herb', 'item_name' => '草药', 'item_type' => 'material', 'rarity' => 'common', 'icon' => 'herb', 'description' => '炼金材料'],
            
            // 副本材料
            ['item_id' => 'dungeon_token', 'item_name' => '副本令牌', 'item_type' => 'dungeon_material', 'rarity' => 'uncommon', 'icon' => 'token_dungeon', 'description' => '副本入场材料'],
            ['item_id' => 'boss_essence', 'item_name' => 'Boss精华', 'item_type' => 'dungeon_material', 'rarity' => 'rare', 'icon' => 'essence_boss', 'description' => 'Boss掉落材料'],
            
            // 装备材料
            ['item_id' => 'enhancement_stone', 'item_name' => '强化石', 'item_type' => 'equipment_material', 'rarity' => 'uncommon', 'icon' => 'stone_enhance', 'description' => '装备强化材料'],
            ['item_id' => 'upgrade_scroll', 'item_name' => '升级卷轴', 'item_type' => 'equipment_material', 'rarity' => 'rare', 'icon' => 'scroll_upgrade', 'description' => '装备升级材料'],
            
            // 宝石
            ['item_id' => 'ruby', 'item_name' => '红宝石', 'item_type' => 'gem', 'rarity' => 'rare', 'icon' => 'gem_ruby', 'description' => '攻击属性宝石'],
            ['item_id' => 'sapphire', 'item_name' => '蓝宝石', 'item_type' => 'gem', 'rarity' => 'rare', 'icon' => 'gem_sapphire', 'description' => '防御属性宝石'],
            ['item_id' => 'emerald', 'item_name' => '翡翠', 'item_type' => 'gem', 'rarity' => 'epic', 'icon' => 'gem_emerald', 'description' => '平衡属性宝石'],
            
            // Boss核心
            ['item_id' => 'dragon_core', 'item_name' => '龙之核心', 'item_type' => 'boss_core', 'rarity' => 'legendary', 'icon' => 'core_dragon', 'description' => '巨龙掉落的核心'],
            ['item_id' => 'demon_heart', 'item_name' => '恶魔之心', 'item_type' => 'boss_core', 'rarity' => 'epic', 'icon' => 'heart_demon', 'description' => '恶魔掉落的核心'],
            
            // 消耗品
            ['item_id' => 'health_potion', 'item_name' => '生命药水', 'item_type' => 'consumable', 'rarity' => 'common', 'icon' => 'potion_health', 'description' => '恢复生命值'],
            ['item_id' => 'mana_potion', 'item_name' => '魔法药水', 'item_type' => 'consumable', 'rarity' => 'common', 'icon' => 'potion_mana', 'description' => '恢复魔法值'],
        ];

        foreach ($items as $item) {
            $item['is_enabled'] = true;
            $item['created_at'] = now();
            $item['updated_at'] = now();
        }

        DB::table('item_configs')->insertOrIgnore($items);
    }
}
