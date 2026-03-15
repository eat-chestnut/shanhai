<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HallFeature extends Model
{
    protected $fillable = [
        'feature_id',
        'feature_name',
        'feature_type',
        'unlock_condition',
        'jump_target',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unlock_condition' => 'array',
            'jump_target' => 'array',
        ];
    }
}
