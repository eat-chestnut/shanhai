<?php

return [
    'starter_player' => [
        'level' => 22,
        'exp' => 480,
        'power' => 0,
        'gold' => 5000,
        'jade' => 300,
        'contribution' => 1200,
        'current_chapter_id' => 'chapter_02',
        'current_node_id' => 'node_04',
        'max_hp' => 1680,
        'max_energy' => 120,
        'skill_points' => 8,
        'class_id' => 'class_jingang',
        'skill_levels' => [
            'skill_jingang_smash' => 2,
            'skill_jingang_quake' => 1,
            'skill_jingang_guard' => 2,
            'skill_jingang_bloodrage' => 1,
            'skill_lingyu_arrow' => 1,
            'skill_lingyu_stormshot' => 1,
            'skill_lingyu_focus' => 1,
            'skill_lingyu_featherstep' => 1,
            'skill_fulu_seal' => 1,
            'skill_fulu_rejuvenation' => 1,
            'skill_fulu_skyfire' => 1,
            'skill_fulu_mastery' => 1,
            'skill_fulu_echo' => 1,
        ],
        'equipment_summary' => [
            'equip_ids' => ['equip_sword_01', 'equip_armor_01'],
            'set_counts' => [['set_id' => 'set_warrior_40', 'equipped_count' => 2]],
            'talisman_star_links' => [['talisman_id' => 'talisman_cloud', 'stars' => 2]],
            'equipped_boss_core_ids' => ['boss_core_qingqiu'],
            'equipped_gem_ids' => ['gem_green', 'gem_yellow'],
            'blue_affix_ids' => ['blue_atk_flat'],
            'purple_refinement_ids' => ['purple_refine_boss'],
        ],
        'inventory' => [
            ['item_id' => 'gem_green', 'count' => 2],
            ['item_id' => 'gem_yellow', 'count' => 1],
            ['item_id' => 'gem_purple', 'count' => 1],
            ['item_id' => 'gem_orange', 'count' => 1],
            ['item_id' => 'blue_atk_flat', 'count' => 1],
            ['item_id' => 'blue_combo_hawk', 'count' => 1],
            ['item_id' => 'purple_refine_boss', 'count' => 1],
            ['item_id' => 'purple_refine_spellburst', 'count' => 1],
            ['item_id' => 'material_seal_essence', 'count' => 4],
            ['item_id' => 'material_seal_crystal', 'count' => 2],
            ['item_id' => 'material_star_stone', 'count' => 8],
            ['item_id' => 'material_star_crystal', 'count' => 2],
            ['item_id' => 'material_refine_sand', 'count' => 5],
            ['item_id' => 'material_refine_crystal', 'count' => 2],
            ['item_id' => 'skill_book_thunder', 'count' => 2],
            ['item_id' => 'equip_bow_01', 'count' => 1],
            ['item_id' => 'equip_staff_01', 'count' => 1],
            ['item_id' => 'equip_warblade_30', 'count' => 1],
            ['item_id' => 'equip_tempest_bow_30', 'count' => 1],
            ['item_id' => 'equip_seal_staff_30', 'count' => 1],
        ],
        'stage_progress' => [
            ['chapter_id' => 'prologue', 'node_id' => 'prologue_node_01', 'difficulty_id' => 'easy', 'is_first_clear' => true, 'clear_count' => 1],
            ['chapter_id' => 'prologue', 'node_id' => 'prologue_node_02', 'difficulty_id' => 'easy', 'is_first_clear' => true, 'clear_count' => 1],
            ['chapter_id' => 'prologue', 'node_id' => 'prologue_node_02', 'difficulty_id' => 'normal', 'is_first_clear' => true, 'clear_count' => 1],
            ['chapter_id' => 'chapter_01', 'node_id' => 'node_01', 'difficulty_id' => 'easy', 'is_first_clear' => true, 'clear_count' => 1],
            ['chapter_id' => 'chapter_01', 'node_id' => 'node_01', 'difficulty_id' => 'normal', 'is_first_clear' => true, 'clear_count' => 1],
            ['chapter_id' => 'chapter_01', 'node_id' => 'node_01', 'difficulty_id' => 'hard', 'is_first_clear' => true, 'clear_count' => 1],
            ['chapter_id' => 'chapter_01', 'node_id' => 'node_02', 'difficulty_id' => 'easy', 'is_first_clear' => true, 'clear_count' => 1],
            ['chapter_id' => 'chapter_01', 'node_id' => 'node_02', 'difficulty_id' => 'normal', 'is_first_clear' => true, 'clear_count' => 1],
            ['chapter_id' => 'chapter_01', 'node_id' => 'node_02', 'difficulty_id' => 'hard', 'is_first_clear' => true, 'clear_count' => 1],
            ['chapter_id' => 'chapter_01', 'node_id' => 'node_03', 'difficulty_id' => 'normal', 'is_first_clear' => true, 'clear_count' => 1],
            ['chapter_id' => 'chapter_01', 'node_id' => 'node_03', 'difficulty_id' => 'hard', 'is_first_clear' => true, 'clear_count' => 1],
            ['chapter_id' => 'chapter_01', 'node_id' => 'node_03', 'difficulty_id' => 'nightmare', 'is_first_clear' => true, 'clear_count' => 1],
        ],
    ],
    'reward_groups' => [
        'reward_node01_easy' => [
            ['item_id' => 'gold', 'count' => 100],
            ['item_id' => 'gem_green', 'count' => 1],
        ],
        'reward_node01_normal' => [
            ['item_id' => 'gold', 'count' => 180],
            ['item_id' => 'gem_green', 'count' => 2],
        ],
        'reward_node01_hard' => [
            ['item_id' => 'gold', 'count' => 300],
            ['item_id' => 'gem_blue', 'count' => 1],
        ],
        'reward_node02_easy' => [
            ['item_id' => 'gold', 'count' => 240],
            ['item_id' => 'material_star_stone', 'count' => 1],
        ],
        'reward_node02_normal' => [
            ['item_id' => 'gold', 'count' => 360],
            ['item_id' => 'material_star_stone', 'count' => 2],
        ],
        'reward_node02_hard' => [
            ['item_id' => 'gold', 'count' => 520],
            ['item_id' => 'gem_yellow', 'count' => 1],
        ],
        'reward_node03_normal' => [
            ['item_id' => 'gold', 'count' => 580],
            ['item_id' => 'material_seal_essence', 'count' => 2],
        ],
        'reward_node03_hard' => [
            ['item_id' => 'gold', 'count' => 720],
            ['item_id' => 'skill_book_thunder', 'count' => 1],
        ],
        'reward_node03_nightmare' => [
            ['item_id' => 'gold', 'count' => 960],
            ['item_id' => 'gem_orange', 'count' => 1],
        ],
        'reward_node04_easy' => [
            ['item_id' => 'gold', 'count' => 660],
            ['item_id' => 'material_star_stone', 'count' => 2],
        ],
        'reward_node04_normal' => [
            ['item_id' => 'gold', 'count' => 780],
            ['item_id' => 'material_seal_essence', 'count' => 2],
        ],
        'reward_node04_hard' => [
            ['item_id' => 'gold', 'count' => 940],
            ['item_id' => 'equip_warblade_30', 'count' => 1],
        ],
        'reward_node05_normal' => [
            ['item_id' => 'gold', 'count' => 980],
            ['item_id' => 'material_seal_crystal', 'count' => 1],
        ],
        'reward_node05_hard' => [
            ['item_id' => 'gold', 'count' => 1160],
            ['item_id' => 'gem_orange', 'count' => 1],
        ],
        'reward_node05_nightmare' => [
            ['item_id' => 'gold', 'count' => 1380],
            ['item_id' => 'equip_tempest_bow_30', 'count' => 1],
        ],
        'reward_node06_hard' => [
            ['item_id' => 'gold', 'count' => 1420],
            ['item_id' => 'material_star_crystal', 'count' => 1],
        ],
        'reward_node06_nightmare' => [
            ['item_id' => 'gold', 'count' => 1680],
            ['item_id' => 'boss_core_thunder', 'count' => 1],
        ],
        'reward_node07_easy' => [
            ['item_id' => 'gold', 'count' => 1320],
            ['item_id' => 'material_refine_sand', 'count' => 2],
        ],
        'reward_node07_normal' => [
            ['item_id' => 'gold', 'count' => 1480],
            ['item_id' => 'blue_combo_hawk', 'count' => 1],
        ],
        'reward_node07_hard' => [
            ['item_id' => 'gold', 'count' => 1720],
            ['item_id' => 'equip_seal_staff_30', 'count' => 1],
        ],
        'reward_node08_normal' => [
            ['item_id' => 'gold', 'count' => 1820],
            ['item_id' => 'material_refine_crystal', 'count' => 1],
        ],
        'reward_node08_hard' => [
            ['item_id' => 'gold', 'count' => 2120],
            ['item_id' => 'purple_refine_spellburst', 'count' => 1],
        ],
        'reward_node08_nightmare' => [
            ['item_id' => 'gold', 'count' => 2460],
            ['item_id' => 'equip_seal_charm_30', 'count' => 1],
        ],
        'reward_node09_hard' => [
            ['item_id' => 'gold', 'count' => 2520],
            ['item_id' => 'material_star_crystal', 'count' => 2],
        ],
        'reward_node09_nightmare' => [
            ['item_id' => 'gold', 'count' => 2920],
            ['item_id' => 'boss_core_abyss', 'count' => 1],
        ],
        'reward_node10_normal' => [
            ['item_id' => 'gold', 'count' => 2860],
            ['item_id' => 'material_refine_crystal', 'count' => 1],
        ],
        'reward_node10_hard' => [
            ['item_id' => 'gold', 'count' => 3240],
            ['item_id' => 'gem_orange', 'count' => 1],
        ],
        'reward_node10_nightmare' => [
            ['item_id' => 'gold', 'count' => 3720],
            ['item_id' => 'equip_abyss_blade', 'count' => 1],
        ],
        'reward_node11_hard' => [
            ['item_id' => 'gold', 'count' => 3980],
            ['item_id' => 'purple_refine_hunter', 'count' => 1],
        ],
        'reward_node11_nightmare' => [
            ['item_id' => 'gold', 'count' => 4420],
            ['item_id' => 'equip_abyss_robe', 'count' => 1],
        ],
        'reward_node12_nightmare' => [
            ['item_id' => 'gold', 'count' => 5200],
            ['item_id' => 'boss_core_abyss', 'count' => 1],
        ],
        'reward_dungeon_gem_easy' => [
            ['item_id' => 'gold', 'count' => 120],
            ['item_id' => 'gem_green', 'count' => 1],
        ],
        'reward_dungeon_gem_normal' => [
            ['item_id' => 'gold', 'count' => 220],
            ['item_id' => 'gem_yellow', 'count' => 1],
        ],
        'reward_dungeon_gem_hard' => [
            ['item_id' => 'gold', 'count' => 320],
            ['item_id' => 'gem_purple', 'count' => 1],
        ],
        'reward_dungeon_gem_nightmare' => [
            ['item_id' => 'gold', 'count' => 620],
            ['item_id' => 'gem_orange', 'count' => 1],
        ],
        'reward_dungeon_seal_easy' => [
            ['item_id' => 'gold', 'count' => 180],
            ['item_id' => 'material_seal_essence', 'count' => 2],
        ],
        'reward_dungeon_seal_normal' => [
            ['item_id' => 'gold', 'count' => 260],
            ['item_id' => 'material_seal_essence', 'count' => 4],
        ],
        'reward_dungeon_seal_hard' => [
            ['item_id' => 'gold', 'count' => 420],
            ['item_id' => 'material_seal_crystal', 'count' => 1],
        ],
        'reward_dungeon_seal_nightmare' => [
            ['item_id' => 'gold', 'count' => 720],
            ['item_id' => 'blue_spell_focus', 'count' => 1],
        ],
        'reward_dungeon_refine_normal' => [
            ['item_id' => 'gold', 'count' => 260],
            ['item_id' => 'material_refine_sand', 'count' => 2],
        ],
        'reward_dungeon_refine_hard' => [
            ['item_id' => 'gold', 'count' => 320],
            ['item_id' => 'material_refine_sand', 'count' => 3],
        ],
        'reward_dungeon_refine_nightmare' => [
            ['item_id' => 'gold', 'count' => 480],
            ['item_id' => 'material_refine_crystal', 'count' => 2],
        ],
        'reward_dungeon_new_hard' => [
            ['item_id' => 'gold', 'count' => 680],
            ['item_id' => 'material_star_crystal', 'count' => 1],
            ['item_id' => 'skill_book_thunder', 'count' => 1],
        ],
        'reward_dungeon_new_nightmare' => [
            ['item_id' => 'gold', 'count' => 980],
            ['item_id' => 'boss_core_thunder', 'count' => 1],
            ['item_id' => 'gem_purple', 'count' => 1],
        ],
        'reward_challenge_floor01_normal' => [
            ['item_id' => 'gold', 'count' => 820],
            ['item_id' => 'material_star_crystal', 'count' => 1],
        ],
        'reward_challenge_floor01_weekly' => [
            ['item_id' => 'contribution', 'count' => 260],
            ['item_id' => 'material_refine_crystal', 'count' => 1],
        ],
        'reward_challenge_floor01_first' => [
            ['item_id' => 'gold', 'count' => 1600],
            ['item_id' => 'boss_core_abyss', 'count' => 1],
        ],
        'reward_challenge_floor02_normal' => [
            ['item_id' => 'gold', 'count' => 980],
            ['item_id' => 'material_refine_crystal', 'count' => 1],
        ],
        'reward_challenge_floor02_weekly' => [
            ['item_id' => 'contribution', 'count' => 340],
            ['item_id' => 'material_seal_crystal', 'count' => 1],
        ],
        'reward_challenge_floor02_first' => [
            ['item_id' => 'gold', 'count' => 1900],
            ['item_id' => 'gem_orange', 'count' => 1],
        ],
        'reward_challenge_floor03_normal' => [
            ['item_id' => 'gold', 'count' => 1140],
            ['item_id' => 'material_star_crystal', 'count' => 1],
            ['item_id' => 'material_refine_crystal', 'count' => 1],
        ],
        'reward_challenge_floor03_weekly' => [
            ['item_id' => 'contribution', 'count' => 420],
            ['item_id' => 'skill_book_thunder', 'count' => 1],
        ],
        'reward_challenge_floor03_first' => [
            ['item_id' => 'gold', 'count' => 2200],
            ['item_id' => 'boss_core_abyss', 'count' => 1],
            ['item_id' => 'material_star_crystal', 'count' => 2],
        ],
        'reward_challenge_floor04_normal' => [
            ['item_id' => 'gold', 'count' => 1320],
            ['item_id' => 'material_star_crystal', 'count' => 2],
        ],
        'reward_challenge_floor04_weekly' => [
            ['item_id' => 'contribution', 'count' => 520],
            ['item_id' => 'material_refine_crystal', 'count' => 2],
        ],
        'reward_challenge_floor04_first' => [
            ['item_id' => 'gold', 'count' => 2600],
            ['item_id' => 'boss_core_abyss', 'count' => 1],
            ['item_id' => 'gem_orange', 'count' => 1],
        ],
    ],
    'encounters' => [
        'stage' => [
            'node_01' => [
                'default' => ['monster_group_id' => 'stage_node_01', 'monster_ids' => ['mon_qingqiu_guard', 'mon_qingqiu_guard', 'mon_qingqiu_boss']],
            ],
            'node_02' => [
                'default' => ['monster_group_id' => 'stage_node_02', 'monster_ids' => ['mon_qingqiu_guard', 'mon_qingqiu_shaman', 'mon_qingqiu_boss']],
            ],
            'node_03' => [
                'default' => ['monster_group_id' => 'stage_node_03', 'monster_ids' => ['mon_qingqiu_shaman', 'mon_qingqiu_boss']],
            ],
            'node_04' => [
                'default' => ['monster_group_id' => 'stage_node_04', 'monster_ids' => ['mon_new_guard', 'mon_thunder_archer', 'mon_new_boss']],
            ],
            'node_05' => [
                'default' => ['monster_group_id' => 'stage_node_05', 'monster_ids' => ['mon_thunder_archer', 'mon_new_guard', 'mon_new_boss']],
            ],
            'node_06' => [
                'default' => ['monster_group_id' => 'stage_node_06', 'monster_ids' => ['mon_new_guard', 'mon_thunder_archer', 'mon_new_boss']],
            ],
            'node_07' => [
                'default' => ['monster_group_id' => 'stage_node_07', 'monster_ids' => ['mon_seal_keeper', 'mon_refine_spirit', 'mon_seal_boss']],
            ],
            'node_08' => [
                'default' => ['monster_group_id' => 'stage_node_08', 'monster_ids' => ['mon_refine_spirit', 'mon_seal_keeper', 'mon_refine_boss']],
            ],
            'node_09' => [
                'default' => ['monster_group_id' => 'stage_node_09', 'monster_ids' => ['mon_refine_spirit', 'mon_refine_boss']],
            ],
            'node_10' => [
                'default' => ['monster_group_id' => 'stage_node_10', 'monster_ids' => ['mon_abyss_guard', 'mon_refine_spirit', 'mon_abyss_lord']],
            ],
            'node_11' => [
                'default' => ['monster_group_id' => 'stage_node_11', 'monster_ids' => ['mon_abyss_guard', 'mon_abyss_guard', 'mon_abyss_lord']],
            ],
            'node_12' => [
                'default' => ['monster_group_id' => 'stage_node_12', 'monster_ids' => ['mon_abyss_lord']],
            ],
        ],
        'dungeon' => [
            'dungeon_gem' => [
                'easy' => ['monster_group_id' => 'dungeon_gem_easy', 'monster_ids' => ['mon_qingqiu_guard', 'mon_qingqiu_guard', 'mon_qingqiu_boss']],
                'normal' => ['monster_group_id' => 'dungeon_gem_normal', 'monster_ids' => ['mon_qingqiu_guard', 'mon_qingqiu_shaman', 'mon_qingqiu_boss']],
                'hard' => ['monster_group_id' => 'dungeon_gem_hard', 'monster_ids' => ['mon_qingqiu_shaman', 'mon_qingqiu_boss']],
                'nightmare' => ['monster_group_id' => 'dungeon_gem_nightmare', 'monster_ids' => ['mon_qingqiu_shaman', 'mon_qingqiu_boss', 'mon_abyss_guard']],
            ],
            'dungeon_seal' => [
                'easy' => ['monster_group_id' => 'dungeon_seal_easy', 'monster_ids' => ['mon_seal_keeper', 'mon_qingqiu_guard', 'mon_seal_boss']],
                'normal' => ['monster_group_id' => 'dungeon_seal_normal', 'monster_ids' => ['mon_seal_keeper', 'mon_seal_keeper', 'mon_seal_boss']],
                'hard' => ['monster_group_id' => 'dungeon_seal_hard', 'monster_ids' => ['mon_seal_keeper', 'mon_refine_spirit', 'mon_seal_boss']],
                'nightmare' => ['monster_group_id' => 'dungeon_seal_nightmare', 'monster_ids' => ['mon_seal_keeper', 'mon_seal_boss', 'mon_abyss_guard']],
            ],
            'dungeon_refine' => [
                'normal' => ['monster_group_id' => 'dungeon_refine_normal', 'monster_ids' => ['mon_refine_spirit', 'mon_refine_boss']],
                'hard' => ['monster_group_id' => 'dungeon_refine_hard', 'monster_ids' => ['mon_refine_spirit', 'mon_refine_spirit', 'mon_refine_boss']],
                'nightmare' => ['monster_group_id' => 'dungeon_refine_nightmare', 'monster_ids' => ['mon_refine_spirit', 'mon_refine_boss', 'mon_abyss_guard']],
            ],
            'dungeon_new' => [
                'hard' => ['monster_group_id' => 'dungeon_new_hard', 'monster_ids' => ['mon_new_guard', 'mon_thunder_archer', 'mon_new_boss']],
                'nightmare' => ['monster_group_id' => 'dungeon_new_nightmare', 'monster_ids' => ['mon_new_guard', 'mon_thunder_archer', 'mon_new_boss', 'mon_abyss_guard']],
            ],
        ],
    ],
    'daily_dungeon_limit' => 3,
    'dungeon_runtime' => [
        'dungeon_gem' => [
            'dungeon_desc' => '宗门灵脉孕玉之地，首版正式循环中作为宝石核心来源。',
            'main_rewards' => ['gem_green', 'gem_yellow', 'gem_purple', 'gem_orange'],
            'daily_limit' => 3,
        ],
        'dungeon_seal' => [
            'dungeon_desc' => '灵印试炼可稳定产出蓝词条提取所需材料。',
            'main_rewards' => ['material_seal_essence', 'material_seal_crystal'],
            'daily_limit' => 3,
            'unlock_stage_node_id' => 'node_02',
        ],
        'dungeon_refine' => [
            'dungeon_desc' => '淬灵秘境掉落紫洗练消耗材料，是成长循环的重要一环。',
            'main_rewards' => ['material_refine_sand', 'material_refine_crystal'],
            'daily_limit' => 2,
            'unlock_stage_node_id' => 'node_04',
        ],
        'dungeon_new' => [
            'dungeon_desc' => '雷鸣谷作为进阶试炼，会额外掉落升星石与 Boss 核心占位奖励。',
            'main_rewards' => ['material_star_stone', 'material_star_crystal', 'boss_core_thunder', 'skill_book_thunder'],
            'daily_limit' => 2,
            'unlock_stage_node_id' => 'node_06',
        ],
    ],
    'equipment_runtime' => [
        'slot_layouts' => [
            'weapon' => ['attribute', 'boss_core'],
            'armor' => ['attribute'],
            'boots' => ['attribute'],
            'helmet' => ['attribute'],
            'accessory' => ['attribute'],
            'default' => ['attribute'],
        ],
        'star_up_item_id' => 'material_star_stone',
        'star_up_cost_base' => 1,
        'blue_extract_item_id' => 'material_seal_essence',
        'blue_extract_advanced_item_id' => 'material_seal_crystal',
        'purple_refine_item_id' => 'material_refine_sand',
        'purple_refine_advanced_item_id' => 'material_refine_crystal',
        'advanced_growth_item_id' => 'material_star_crystal',
        'advanced_level_threshold' => 60,
        'advanced_star_threshold' => 5,
    ],
];
