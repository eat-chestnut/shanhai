<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $primaryKey = 'item_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'item_id',
        'item_name',
        'item_type',
        'rarity',
        'icon',
        'desc',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * 获取物品类型选项
     */
    public static function getItemTypeOptions(): array
    {
        return [
            'currency' => '货币',
            'material' => '材料',
            'equipment' => '装备',
            'consumable' => '消耗品',
            'boss_material' => 'Boss材料',
            'quest_item' => '任务物品',
            'fragment' => '碎片',
            'gem' => '宝石',
            'recipe' => '配方',
        ];
    }

    /**
     * 获取稀有度选项
     */
    public static function getRarityOptions(): array
    {
        return [
            'common' => '普通',
            'uncommon' => '优秀',
            'rare' => '稀有',
            'epic' => '史诗',
            'legendary' => '传说',
        ];
    }
}
