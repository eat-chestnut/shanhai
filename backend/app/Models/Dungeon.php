<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dungeon extends Model
{
    protected $fillable = [
        'dungeon_id',
        'dungeon_name',
        'unlock_level',
    ];

    public function difficulties(): HasMany
    {
        return $this->hasMany(DungeonDifficulty::class, 'dungeon_id', 'dungeon_id');
    }
}
