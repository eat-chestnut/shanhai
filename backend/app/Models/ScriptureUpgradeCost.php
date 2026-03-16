<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScriptureUpgradeCost extends Model
{
    protected $fillable = [
        'scripture_id',
        'target_world_level',
        'cost_items',
        'cost_gold',
        'required_player_level',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_world_level' => 'integer',
            'cost_items' => 'array',
            'cost_gold' => 'integer',
            'required_player_level' => 'integer',
        ];
    }
}
