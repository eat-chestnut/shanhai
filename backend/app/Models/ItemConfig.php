<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemConfig extends Model
{
    protected $fillable = [
        'item_id',
        'item_name',
        'item_type',
        'rarity',
        'icon',
        'description',
        'is_enabled',
        'extra_data',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'extra_data' => 'array',
        ];
    }

    public function rarityConfig()
    {
        return $this->belongsTo(RarityConfig::class, 'rarity', 'rarity_key');
    }
}
