<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Skill extends Model
{
    protected $fillable = [
        'skill_id',
        'class_id',
        'skill_name',
        'skill_desc',
        'type',
        'effect_type',
        'target_type',
        'cooldown',
        'cost',
        'unlock_level',
        'max_level',
        'power_base',
        'power_per_level',
        'duration',
        'chance',
        'stat_bonuses',
        'effect_payload',
        'is_open',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cooldown' => 'integer',
            'cost' => 'integer',
            'unlock_level' => 'integer',
            'max_level' => 'integer',
            'power_base' => 'integer',
            'power_per_level' => 'integer',
            'duration' => 'integer',
            'chance' => 'float',
            'stat_bonuses' => 'array',
            'effect_payload' => 'array',
            'is_open' => 'boolean',
        ];
    }

    public function characterClass(): BelongsTo
    {
        return $this->belongsTo(CharacterClass::class, 'class_id', 'class_id');
    }
}
