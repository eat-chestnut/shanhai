<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScriptureWorldTier extends Model
{
    protected $fillable = [
        'scripture_id',
        'world_level_start',
        'world_level_end',
        'hp_scale',
        'atk_scale',
        'def_scale',
        'reward_scale',
        'gold_scale',
        'normal_monster_ids',
        'elite_monster_ids',
        'boss_monster_ids',
        'extra_drop_tags',
        'new_feature_note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'world_level_start' => 'integer',
            'world_level_end' => 'integer',
            'hp_scale' => 'float',
            'atk_scale' => 'float',
            'def_scale' => 'float',
            'reward_scale' => 'float',
            'gold_scale' => 'float',
            'normal_monster_ids' => 'array',
            'elite_monster_ids' => 'array',
            'boss_monster_ids' => 'array',
            'extra_drop_tags' => 'array',
        ];
    }

    public function matchesWorldLevel(int $worldLevel): bool
    {
        return $worldLevel >= (int) $this->world_level_start && $worldLevel <= (int) $this->world_level_end;
    }
}
