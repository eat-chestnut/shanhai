<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScriptureDropTag extends Model
{
    protected $primaryKey = 'drop_tag';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'drop_tag',
        'tag_name',
        'items',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'items' => 'array',
        ];
    }

    public static function getDropTagOptions(): array
    {
        return static::query()
            ->orderBy('drop_tag')
            ->get()
            ->mapWithKeys(static fn (self $tag): array => [
                $tag->drop_tag => "{$tag->drop_tag} / {$tag->tag_name}",
            ])
            ->all();
    }
}
