<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerItem extends Model
{
    protected $fillable = [
        'player_id',
        'item_id',
        'count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'player_id' => 'integer',
            'count' => 'integer',
        ];
    }
}
