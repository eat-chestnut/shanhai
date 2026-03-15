<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gem extends Model
{
    protected $fillable = [
        'gem_id',
        'name',
        'type',
        'bonus_atk',
        'bonus_boss_dmg',
    ];
}
