<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeConfig extends Model
{
    protected $fillable = [
        'challenge_id',
        'challenge_name',
        'challenge_type',
        'challenge_desc',
        'unlock_level',
        'cycle_type',
        'floors',
        'reward_preview',
        'sort',
        'is_open',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unlock_level' => 'integer',
            'floors' => 'array',
            'reward_preview' => 'array',
            'sort' => 'integer',
            'is_open' => 'boolean',
        ];
    }
}
