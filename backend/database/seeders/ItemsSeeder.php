<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // 货币类
            [
                'item_id' => 'gold',
                'item_name' => '金币',
                'item_type' => 'currency',
                'rarity' => 'common',
                'icon' => 'icon_gold',
                'desc' => '游戏中的通用货币，用于购买各种物品和服务。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'contribution',
                'item_name' => '贡献度',
                'item_type' => 'currency',
                'rarity' => 'uncommon',
                'icon' => 'icon_contribution',
                'desc' => '通过完成任务和参与活动获得的贡献点数。',
                'is_enabled' => true,
            ],
            
            // 材料类
            [
                'item_id' => 'material_star_stone',
                'item_name' => '星石',
                'item_type' => 'material',
                'rarity' => 'uncommon',
                'icon' => 'icon_star_stone',
                'desc' => '蕴含星力的神秘石头，用于装备强化。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'material_seal_essence',
                'item_name' => '封印精华',
                'item_type' => 'material',
                'rarity' => 'uncommon',
                'icon' => 'icon_seal_essence',
                'desc' => '封印力量的精华材料。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'material_star_crystal',
                'item_name' => '星晶',
                'item_type' => 'material',
                'rarity' => 'rare',
                'icon' => 'icon_star_crystal',
                'desc' => '高度浓缩的星力结晶，珍贵材料。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'material_refine_sand',
                'item_name' => '炼化沙',
                'item_type' => 'material',
                'rarity' => 'rare',
                'icon' => 'icon_refine_sand',
                'desc' => '用于装备炼化的特殊沙粒。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'material_refine_crystal',
                'item_name' => '炼化晶',
                'item_type' => 'material',
                'rarity' => 'epic',
                'icon' => 'icon_refine_crystal',
                'desc' => '强大的炼化材料，能大幅提升装备属性。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'material_seal_crystal',
                'item_name' => '封印晶',
                'item_type' => 'material',
                'rarity' => 'epic',
                'icon' => 'icon_seal_crystal',
                'desc' => '封印强力的晶体材料。',
                'is_enabled' => true,
            ],
            
            // Boss材料类
            [
                'item_id' => 'boss_core_abyss',
                'item_name' => '深渊核心',
                'item_type' => 'boss_material',
                'rarity' => 'epic',
                'icon' => 'icon_abyss_core',
                'desc' => '深渊Boss掉落的珍贵核心材料。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'boss_core_qingqiu',
                'item_name' => '青丘核心',
                'item_type' => 'boss_material',
                'rarity' => 'epic',
                'icon' => 'icon_qingqiu_core',
                'desc' => '青丘Boss掉落的珍贵核心材料。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'boss_core_thunder',
                'item_name' => '雷鸣核心',
                'item_type' => 'boss_material',
                'rarity' => 'epic',
                'icon' => 'icon_thunder_core',
                'desc' => '雷鸣Boss掉落的珍贵核心材料。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'boss_core_test',
                'item_name' => '测试核心',
                'item_type' => 'boss_material',
                'rarity' => 'rare',
                'icon' => 'icon_test_core',
                'desc' => '测试Boss掉落的核心材料。',
                'is_enabled' => true,
            ],
            
            // 宝石类
            [
                'item_id' => 'gem_gold',
                'item_name' => '金宝石',
                'item_type' => 'gem',
                'rarity' => 'uncommon',
                'icon' => 'icon_gem_gold',
                'desc' => '闪耀金色光芒的宝石。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'gem_green',
                'item_name' => '绿宝石',
                'item_type' => 'gem',
                'rarity' => 'uncommon',
                'icon' => 'icon_gem_green',
                'desc' => '翠绿色的宝石，蕴含自然之力。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'gem_blue',
                'item_name' => '蓝宝石',
                'item_type' => 'gem',
                'rarity' => 'rare',
                'icon' => 'icon_gem_blue',
                'desc' => '深邃的蓝色宝石，蕴含水元素之力。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'gem_yellow',
                'item_name' => '黄宝石',
                'item_type' => 'gem',
                'rarity' => 'rare',
                'icon' => 'icon_gem_yellow',
                'desc' => '明亮的黄色宝石，蕴含土元素之力。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'gem_orange',
                'item_name' => '橙宝石',
                'item_type' => 'gem',
                'rarity' => 'epic',
                'icon' => 'icon_gem_orange',
                'desc' => '炽热的橙色宝石，蕴含火元素之力。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'gem_purple',
                'item_name' => '紫宝石',
                'item_type' => 'gem',
                'rarity' => 'epic',
                'icon' => 'icon_gem_purple',
                'desc' => '神秘的紫色宝石，蕴含暗元素之力。',
                'is_enabled' => true,
            ],
            
            // 装备类
            [
                'item_id' => 'equip_tempest_bow_30',
                'item_name' => '风暴弓',
                'item_type' => 'equipment',
                'rarity' => 'rare',
                'icon' => 'icon_tempest_bow',
                'desc' => '30级风暴弓，射出风系箭矢。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'equip_abyss_blade',
                'item_name' => '深渊之刃',
                'item_type' => 'equipment',
                'rarity' => 'epic',
                'icon' => 'icon_abyss_blade',
                'desc' => '深渊锻造的强力武器。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'equip_abyss_robe',
                'item_name' => '深渊法袍',
                'item_type' => 'equipment',
                'rarity' => 'epic',
                'icon' => 'icon_abyss_robe',
                'desc' => '深渊法师的专用法袍。',
                'is_enabled' => true,
            ],
            
            // 技能书类
            [
                'item_id' => 'skill_book_thunder',
                'item_name' => '雷击术',
                'item_type' => 'consumable',
                'rarity' => 'rare',
                'icon' => 'icon_thunder_book',
                'desc' => '学习雷击术的技能书。',
                'is_enabled' => true,
            ],
            
            // 特殊物品
            [
                'item_id' => 'purple_refine_spellburst',
                'item_name' => '紫色炼化法术',
                'item_type' => 'consumable',
                'rarity' => 'epic',
                'icon' => 'icon_purple_spell',
                'desc' => '紫色品质的炼化法术卷轴。',
                'is_enabled' => true,
            ],
            [
                'item_id' => 'blue_combo_hawk',
                'item_name' => '蓝鹰连击',
                'item_type' => 'consumable',
                'rarity' => 'rare',
                'icon' => 'icon_blue_hawk',
                'desc' => '召唤蓝鹰进行连击的卷轴。',
                'is_enabled' => true,
            ],
        ];

        DB::table('items')->insertOrIgnore($items);
    }
}
