<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerShopPurchase extends Model
{
    protected $table = 'player_shop_purchases';

    protected $fillable = [
        'player_id',
        'shop_item_id',
        'cycle_key',
        'bought_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'player_id' => 'integer',
            'bought_count' => 'integer',
        ];
    }
}
