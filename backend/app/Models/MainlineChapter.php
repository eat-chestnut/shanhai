<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MainlineChapter extends Model
{
    protected $fillable = [
        'chapter_id',
        'chapter_name',
        'scripture_id',
        'unlock_level',
        'sort_order',
        'required_previous_chapter',
        'required_previous_highest_difficulty',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unlock_level' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(MainlineNode::class, 'chapter_id', 'chapter_id');
    }
}
