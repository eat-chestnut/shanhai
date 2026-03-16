<?php

return [
    'starter_player' => [
        'level' => 22,
        'exp' => 480,
        'power' => 0,
        'gold' => 5000,
        'jade' => 300,
        'contribution' => 1200,
        'current_chapter_id' => 'chapter_01',
        'current_node_id' => 'node_01',
        'max_hp' => 1680,
        'max_energy' => 120,
        'skill_points' => 6,
        'class_id' => 'class_jingang',
        'skill_levels' => [
            'skill_jingang_smash' => 2,
            'skill_jingang_guard' => 2,
            'skill_lingyu_arrow' => 1,
            'skill_lingyu_focus' => 1,
            'skill_fulu_seal' => 1,
            'skill_fulu_rejuvenation' => 1,
            'skill_fulu_mastery' => 1,
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
            ['item_id' => 'blue_atk_flat', 'count' => 1],
            ['item_id' => 'purple_refine_boss', 'count' => 1],
            ['item_id' => 'material_star_stone', 'count' => 8],
            ['item_id' => 'material_refine_sand', 'count' => 5],
            ['item_id' => 'skill_book_thunder', 'count' => 2],
            ['item_id' => 'equip_bow_01', 'count' => 1],
            ['item_id' => 'equip_staff_01', 'count' => 1],
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
        'reward_dungeon_seal_easy' => [
            ['item_id' => 'gold', 'count' => 180],
            ['item_id' => 'material_refine_sand', 'count' => 2],
        ],
        'reward_dungeon_seal_normal' => [
            ['item_id' => 'gold', 'count' => 260],
            ['item_id' => 'material_refine_sand', 'count' => 4],
        ],
        'reward_dungeon_refine_hard' => [
            ['item_id' => 'gold', 'count' => 320],
            ['item_id' => 'material_star_stone', 'count' => 3],
        ],
        'reward_dungeon_refine_nightmare' => [
            ['item_id' => 'gold', 'count' => 480],
            ['item_id' => 'material_star_stone', 'count' => 5],
        ],
        'reward_dungeon_new_easy' => [
            ['item_id' => 'gold', 'count' => 260],
            ['item_id' => 'gem_yellow', 'count' => 1],
            ['item_id' => 'material_star_stone', 'count' => 1],
        ],
        'reward_dungeon_new_normal' => [
            ['item_id' => 'gold', 'count' => 420],
            ['item_id' => 'skill_book_thunder', 'count' => 1],
            ['item_id' => 'material_refine_sand', 'count' => 2],
        ],
        'reward_dungeon_new_hard' => [
            ['item_id' => 'gold', 'count' => 680],
            ['item_id' => 'boss_core_thunder', 'count' => 1],
            ['item_id' => 'gem_purple', 'count' => 1],
        ],
    ],
    'encounters' => [
        'stage' => [
            'node_01' => ['mon_qingqiu_guard', 'mon_qingqiu_guard', 'mon_qingqiu_boss'],
        ],
        'dungeon' => [
            'dungeon_gem' => ['mon_qingqiu_guard', 'mon_qingqiu_guard', 'mon_qingqiu_boss'],
            'dungeon_seal' => ['mon_qingqiu_guard', 'mon_qingqiu_boss'],
            'dungeon_refine' => ['mon_qingqiu_boss'],
            'dungeon_new' => ['mon_new_guard', 'mon_new_guard', 'mon_new_boss'],
        ],
    ],
    'daily_dungeon_limit' => 99,
];
