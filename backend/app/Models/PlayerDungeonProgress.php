<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerDungeonProgress extends Model
{
    protected $fillable = [
        'player_id',
        'dungeon_id',
        'difficulty_id',
        'is_first_clear',
        'clear_count',
        'daily_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'player_id' => 'integer',
            'is_first_clear' => 'boolean',
            'clear_count' => 'integer',
            'daily_count' => 'integer',
        ];
    }
}
