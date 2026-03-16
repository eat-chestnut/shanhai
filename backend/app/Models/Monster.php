<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monster extends Model
{
    protected $fillable = [
        'monster_id',
        'name',
        'combat_role',
        'base_hp',
        'base_atk',
        'is_boss',
        'behavior_profile',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_boss' => 'boolean',
            'behavior_profile' => 'array',
        ];
    }

    public function drops(): HasMany
    {
        return $this->hasMany(MonsterDrop::class, 'monster_id', 'monster_id');
    }
}
