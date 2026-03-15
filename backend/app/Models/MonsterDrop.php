<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonsterDrop extends Model
{
    protected $fillable = [
        'monster_id',
        'item_id',
        'drop_rate',
        'drop_kind',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'drop_rate' => 'float',
        ];
    }

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class, 'monster_id', 'monster_id');
    }
}
