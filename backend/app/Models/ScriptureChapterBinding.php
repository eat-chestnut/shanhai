<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScriptureChapterBinding extends Model
{
    protected $fillable = [
        'scripture_id',
        'chapter_id',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
