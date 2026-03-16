<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdleRewardRule extends Model
{
    protected $fillable = [
        'rule_id',
        'rule_name',
        'min_level',
        'max_level',
        'idle_cap_hours',
        'reward_rate',
        'bonus_hint',
        'sort',
        'is_open',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_level' => 'integer',
            'max_level' => 'integer',
            'idle_cap_hours' => 'integer',
            'reward_rate' => 'array',
            'sort' => 'integer',
            'is_open' => 'boolean',
        ];
    }
}
