<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BattleRecord extends Model
{
    protected $fillable = [
        'battle_id',
        'player_id',
        'source_type',
        'source_id',
        'difficulty_id',
        'status',
        'result',
        'duration',
        'cleared_wave',
        'battle_map_id',
        'battle_seed',
        'request_snapshot',
        'player_snapshot',
        'enemy_group_snapshot',
        'settle_payload',
        'rewards',
        'first_clear_rewards',
        'settled_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'player_id' => 'integer',
            'duration' => 'integer',
            'cleared_wave' => 'integer',
            'battle_seed' => 'integer',
            'request_snapshot' => 'array',
            'player_snapshot' => 'array',
            'enemy_group_snapshot' => 'array',
            'settle_payload' => 'array',
            'rewards' => 'array',
            'first_clear_rewards' => 'array',
            'settled_at' => 'datetime',
        ];
    }
}
