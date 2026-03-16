<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Throwable;

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
            'talisman' => '护符',
            'consumable' => '消耗品',
            'boss_material' => 'Boss材料',
            'quest_item' => '任务物品',
            'fragment' => '碎片',
            'gem' => '宝石',
            'recipe' => '配方',
            'blue_affix' => '蓝词条',
            'purple_refinement' => '紫炼化',
        ];
    }

    /**
     * 获取稀有度选项
     */
    public static function getRarityOptions(): array
    {
        try {
            $options = RarityConfig::query()
                ->where('is_enabled', true)
                ->orderBy('sort')
                ->orderBy('rarity_key')
                ->pluck('rarity_name', 'rarity_key')
                ->all();

            if ($options !== []) {
                return $options;
            }
        } catch (Throwable) {
            // Fresh migration / seed early boot falls back to default labels.
        }

        return collect(RarityConfig::getDefaultConfigs())
            ->mapWithKeys(static fn (array $config): array => [
                (string) $config['rarity_key'] => (string) $config['rarity_name'],
            ])
            ->all();
    }

    public static function getEnabledItemOptions(): array
    {
        try {
            return static::query()
                ->where('is_enabled', true)
                ->orderBy('item_type')
                ->orderBy('item_name')
                ->get()
                ->mapWithKeys(static fn (self $item): array => [
                    $item->item_id => "{$item->item_id} / {$item->item_name}",
                ])
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    public static function formatItemType(string $itemType): string
    {
        return static::getItemTypeOptions()[$itemType] ?? $itemType;
    }

    public static function formatRarity(string $rarity): string
    {
        return static::getRarityOptions()[$rarity] ?? $rarity;
    }
}
