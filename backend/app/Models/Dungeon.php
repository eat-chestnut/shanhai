<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dungeon extends Model
{
    protected $fillable = [
        'dungeon_id',
        'dungeon_name',
        'dungeon_desc',
        'unlock_level',
        'main_rewards',
        'daily_limit',
        'unlock_stage_node_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'main_rewards' => 'array',
        ];
    }

    public function difficulties(): HasMany
    {
        return $this->hasMany(DungeonDifficulty::class, 'dungeon_id', 'dungeon_id');
    }
}
