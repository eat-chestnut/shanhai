<?php

use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 基础货币
        App\Models\Item::updateOrCreate(['item_id' => 'gold_coin'], [
            'item_name' => '金币',
            'item_type' => 'currency',
            'rarity' => 'common',
            'icon' => 'icon_gold_coin',
            'desc' => '通用货币，用于购买各种物品',
            'is_enabled' => true,
        ]);

        App\Models\Item::updateOrCreate(['item_id' => 'jade'], [
            'item_name' => '灵玉',
            'item_type' => 'currency',
            'rarity' => 'rare',
            'icon' => 'icon_jade',
            'desc' => '高级货币，用于购买珍贵物品',
            'is_enabled' => true,
        ]);

        // 基础材料
        App\Models\Item::updateOrCreate(['item_id' => 'iron_ore'], [
            'item_name' => '铁矿石',
            'item_type' => 'material',
            'rarity' => 'common',
            'icon' => 'icon_iron_ore',
            'desc' => '基础锻造材料',
            'is_enabled' => true,
        ]);

        App\Models\Item::updateOrCreate(['item_id' => 'jade_fragment'], [
            'item_name' => '灵玉碎片',
            'item_type' => 'material',
            'rarity' => 'rare',
            'icon' => 'icon_jade_fragment',
            'desc' => '用于宝石相关成长',
            'is_enabled' => true,
        ]);

        // Boss材料
        App\Models\Item::updateOrCreate(['item_id' => 'boss_core_fire'], [
            'item_name' => '炎核',
            'item_type' => 'boss_material',
            'rarity' => 'epic',
            'icon' => 'icon_boss_core_fire',
            'desc' => '火系Boss核心材料',
            'is_enabled' => true,
        ]);

        App\Models\Item::updateOrCreate(['item_id' => 'boss_core_ice'], [
            'item_name' => '冰核',
            'item_type' => 'boss_material',
            'rarity' => 'epic',
            'icon' => 'icon_boss_core_ice',
            'desc' => '冰系Boss核心材料',
            'is_enabled' => true,
        ]);

        // 宝石
        App\Models\Item::updateOrCreate(['item_id' => 'ruby'], [
            'item_name' => '红宝石',
            'item_type' => 'gem',
            'rarity' => 'rare',
            'icon' => 'icon_ruby',
            'desc' => '增加攻击力的宝石',
            'is_enabled' => true,
        ]);

        App\Models\Item::updateOrCreate(['item_id' => 'sapphire'], [
            'item_name' => '蓝宝石',
            'item_type' => 'gem',
            'rarity' => 'rare',
            'icon' => 'icon_sapphire',
            'desc' => '增加防御力的宝石',
            'is_enabled' => true,
        ]);

        // 消耗品
        App\Models\Item::updateOrCreate(['item_id' => 'health_potion'], [
            'item_name' => '生命药水',
            'item_type' => 'consumable',
            'rarity' => 'common',
            'icon' => 'icon_health_potion',
            'desc' => '恢复生命值的药水',
            'is_enabled' => true,
        ]);

        App\Models\Item::updateOrCreate(['item_id' => 'mana_potion'], [
            'item_name' => '法力药水',
            'item_type' => 'consumable',
            'rarity' => 'common',
            'icon' => 'icon_mana_potion',
            'desc' => '恢复法力值的药水',
            'is_enabled' => true,
        ]);

        // 装备
        App\Models\Item::updateOrCreate(['item_id' => 'iron_sword'], [
            'item_name' => '铁剑',
            'item_type' => 'equipment',
            'rarity' => 'common',
            'icon' => 'icon_iron_sword',
            'desc' => '基础武器，增加少量攻击力',
            'is_enabled' => true,
        ]);

        App\Models\Item::updateOrCreate(['item_id' => 'steel_armor'], [
            'item_name' => '钢甲',
            'item_type' => 'equipment',
            'rarity' => 'uncommon',
            'icon' => 'icon_steel_armor',
            'desc' => '基础护甲，增加少量防御力',
            'is_enabled' => true,
        ]);
    }
}
