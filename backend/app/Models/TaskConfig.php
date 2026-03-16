<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskConfig extends Model
{
    protected $fillable = [
        'task_id',
        'task_type',
        'task_name',
        'task_desc',
        'target_type',
        'target',
        'conditions',
        'rewards',
        'sort',
        'is_open',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target' => 'integer',
            'conditions' => 'array',
            'rewards' => 'array',
            'sort' => 'integer',
            'is_open' => 'boolean',
        ];
    }
}
