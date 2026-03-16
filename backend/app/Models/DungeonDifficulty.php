<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DungeonDifficulty extends Model
{
    protected $fillable = [
        'difficulty_id',
        'dungeon_id',
        'recommended_power',
        'first_clear_reward_group_id',
    ];

    public function dungeon(): BelongsTo
    {
        return $this->belongsTo(Dungeon::class, 'dungeon_id', 'dungeon_id');
    }
}
