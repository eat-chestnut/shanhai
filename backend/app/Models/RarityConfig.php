<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RarityConfig extends Model
{
    protected $fillable = [
        'rarity_key',
        'rarity_name',
        'sort_order',
        'text_color',
        'bg_color',
        'border_color',
        'glow_color',
        'frame_key',
        'is_enabled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemConfig::class, 'rarity', 'rarity_key');
    }
}
