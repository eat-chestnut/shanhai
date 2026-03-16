<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerEquipment extends Model
{
    protected $table = 'player_equipments';

    protected $fillable = [
        'equipment_uid',
        'player_id',
        'equip_id',
        'slot_type',
        'star_level',
        'gem_slots_json',
        'blue_affix_id',
        'purple_refinement_id',
        'is_equipped',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'player_id' => 'integer',
            'star_level' => 'integer',
            'gem_slots_json' => 'array',
            'is_equipped' => 'boolean',
        ];
    }
}
