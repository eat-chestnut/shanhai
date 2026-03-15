<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurpleRefinement extends Model
{
    protected $fillable = [
        'refinement_id',
        'name',
        'bonuses',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bonuses' => 'array',
        ];
    }
}
