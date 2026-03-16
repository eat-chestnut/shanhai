<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RarityConfig extends Model
{
    protected $primaryKey = 'rarity_key';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'rarity_key',
        'rarity_name',
        'sort',
        'text_color',
        'bg_color',
        'border_color',
        'frame_key',
        'is_enabled',
    ];

    protected $casts = [
        'sort' => 'integer',
        'is_enabled' => 'boolean',
    ];

    /**
     * 获取默认稀有度配置
     */
    public static function getDefaultConfigs(): array
    {
        return [
            [
                'rarity_key' => 'common',
                'rarity_name' => '普通',
                'sort' => 1,
                'text_color' => '#FFFFFF',
                'bg_color' => '#2F2F2F',
                'border_color' => '#7A7A7A',
                'frame_key' => 'frame_common',
                'is_enabled' => true,
            ],
            [
                'rarity_key' => 'uncommon',
                'rarity_name' => '优秀',
                'sort' => 2,
                'text_color' => '#D8F3FF',
                'bg_color' => '#163A59',
                'border_color' => '#4DB3FF',
                'frame_key' => 'frame_uncommon',
                'is_enabled' => true,
            ],
            [
                'rarity_key' => 'rare',
                'rarity_name' => '稀有',
                'sort' => 3,
                'text_color' => '#D8F3FF',
                'bg_color' => '#163A59',
                'border_color' => '#4DB3FF',
                'frame_key' => 'frame_rare',
                'is_enabled' => true,
            ],
            [
                'rarity_key' => 'epic',
                'rarity_name' => '史诗',
                'sort' => 4,
                'text_color' => '#F3E2FF',
                'bg_color' => '#4A235A',
                'border_color' => '#C56CFF',
                'frame_key' => 'frame_epic',
                'is_enabled' => true,
            ],
            [
                'rarity_key' => 'legendary',
                'rarity_name' => '传说',
                'sort' => 5,
                'text_color' => '#FFE8D6',
                'bg_color' => '#6B4423',
                'border_color' => '#FFB84D',
                'frame_key' => 'frame_legendary',
                'is_enabled' => true,
            ],
        ];
    }
}
