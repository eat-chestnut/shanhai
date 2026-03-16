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
        'normal_monster_pool',
        'elite_monster_pool',
        'boss_monster_pool',
        'normal_spawn_interval',
        'normal_spawn_count',
        'max_normal_on_screen',
        'elite_trigger_kills',
        'boss_trigger_elite_kills',
        'stop_spawning_after_boss',
        'clear_dungeon_after_boss',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'normal_monster_pool' => 'array',
            'elite_monster_pool' => 'array',
            'boss_monster_pool' => 'array',
            'stop_spawning_after_boss' => 'boolean',
            'clear_dungeon_after_boss' => 'boolean',
        ];
    }

    public function dungeon(): BelongsTo
    {
        return $this->belongsTo(Dungeon::class, 'dungeon_id', 'dungeon_id');
    }
}
