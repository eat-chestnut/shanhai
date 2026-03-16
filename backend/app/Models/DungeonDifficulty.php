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
        'normal_monster_ids',
        'normal_spawn_interval',
        'normal_spawn_count',
        'normal_alive_limit',
        'elite_monster_ids',
        'elite_spawn_interval',
        'elite_spawn_count',
        'elite_alive_limit',
        'boss_monster_id',
        'normal_kill_to_spawn_elite',
        'elite_kill_to_spawn_boss',
        'stop_spawn_after_boss_appears',
        'clear_on_boss_killed',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'normal_monster_ids' => 'array',
            'elite_monster_ids' => 'array',
            'stop_spawn_after_boss_appears' => 'boolean',
            'clear_on_boss_killed' => 'boolean',
        ];
    }

    public function dungeon(): BelongsTo
    {
        return $this->belongsTo(Dungeon::class, 'dungeon_id', 'dungeon_id');
    }
}
