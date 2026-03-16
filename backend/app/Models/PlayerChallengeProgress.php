<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerChallengeProgress extends Model
{
    protected $fillable = [
        'player_id',
        'challenge_id',
        'week_key',
        'highest_floor',
        'current_floor',
        'weekly_highest_floor',
        'clear_count',
        'weekly_clear_count',
        'first_clear_floors',
        'weekly_reward_claimed_floors',
        'last_cleared_floor',
        'last_cleared_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'player_id' => 'integer',
            'highest_floor' => 'integer',
            'current_floor' => 'integer',
            'weekly_highest_floor' => 'integer',
            'clear_count' => 'integer',
            'weekly_clear_count' => 'integer',
            'first_clear_floors' => 'array',
            'weekly_reward_claimed_floors' => 'array',
            'last_cleared_floor' => 'integer',
            'last_cleared_at' => 'datetime',
        ];
    }
}
