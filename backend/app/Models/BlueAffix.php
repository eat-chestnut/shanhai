<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlueAffix extends Model
{
    protected $fillable = [
        'affix_id',
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
