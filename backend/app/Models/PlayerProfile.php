<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerProfile extends Model
{
    protected $fillable = [
        'player_id',
        'nickname',
        'auth_token',
        'class_id',
        'level',
        'exp',
        'power',
        'gold',
        'jade',
        'contribution',
        'current_chapter_id',
        'current_node_id',
        'max_hp',
        'max_energy',
        'skill_points',
        'skill_levels',
        'equipment_summary',
        'last_login_at',
        'idle_started_at',
        'idle_last_claimed_at',
        'last_active_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'exp' => 'integer',
            'power' => 'integer',
            'gold' => 'integer',
            'jade' => 'integer',
            'contribution' => 'integer',
            'max_hp' => 'integer',
            'max_energy' => 'integer',
            'skill_points' => 'integer',
            'skill_levels' => 'array',
            'equipment_summary' => 'array',
            'last_login_at' => 'datetime',
            'idle_started_at' => 'datetime',
            'idle_last_claimed_at' => 'datetime',
            'last_active_at' => 'datetime',
        ];
    }
}
