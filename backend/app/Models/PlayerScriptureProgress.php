<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerScriptureProgress extends Model
{
    protected $fillable = [
        'player_id',
        'scripture_id',
        'current_world_level',
        'max_unlocked_world_level',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'player_id' => 'integer',
            'current_world_level' => 'integer',
            'max_unlocked_world_level' => 'integer',
        ];
    }
}
