<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentSet extends Model
{
    protected $fillable = [
        'set_id',
        'level',
        'pieces',
        'effects',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pieces' => 'array',
            'effects' => 'array',
        ];
    }
}
