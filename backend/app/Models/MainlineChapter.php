<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MainlineChapter extends Model
{
    protected $fillable = [
        'chapter_id',
        'chapter_name',
        'unlock_level',
        'sort_order',
        'required_previous_chapter',
        'required_previous_highest_difficulty',
    ];

    public function nodes(): HasMany
    {
        return $this->hasMany(MainlineNode::class, 'chapter_id', 'chapter_id');
    }
}
