<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = [
        'equip_id',
        'name',
        'type',
        'level',
        'base_atk',
        'base_def',
    ];
}
