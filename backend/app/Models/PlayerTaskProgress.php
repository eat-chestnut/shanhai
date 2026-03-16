<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerTaskProgress extends Model
{
    protected $table = 'player_task_progress';

    protected $fillable = [
        'player_id',
        'task_id',
        'cycle_key',
        'progress',
        'is_claimed',
        'claimed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'player_id' => 'integer',
            'progress' => 'integer',
            'is_claimed' => 'boolean',
            'claimed_at' => 'datetime',
        ];
    }
}
