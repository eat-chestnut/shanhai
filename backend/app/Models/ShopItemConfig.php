<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopItemConfig extends Model
{
    protected $fillable = [
        'shop_item_id',
        'shop_type',
        'item_id',
        'item_name',
        'count',
        'cost_type',
        'cost_value',
        'buy_limit',
        'cycle_type',
        'sort',
        'is_open',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'count' => 'integer',
            'cost_value' => 'integer',
            'buy_limit' => 'integer',
            'sort' => 'integer',
            'is_open' => 'boolean',
        ];
    }
}
