<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MainlineNode extends Model
{
    protected $fillable = [
        'node_id',
        'chapter_id',
        'node_name',
        'unlock_condition',
        'difficulty_ids',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unlock_condition' => 'array',
            'difficulty_ids' => 'array',
        ];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(MainlineChapter::class, 'chapter_id', 'chapter_id');
    }

    public function difficulties(): HasMany
    {
        return $this->hasMany(MainlineDifficulty::class, 'node_id', 'node_id');
    }

    public static function syncDifficultyIdsForNode(string $nodeId): void
    {
        $node = static::query()->where('node_id', $nodeId)->first();

        if (! $node) {
            return;
        }

        $difficultyIds = $node->difficulties()
            ->orderBy('difficulty_order')
            ->orderBy('difficulty_id')
            ->pluck('difficulty_id')
            ->all();

        $node->forceFill([
            'difficulty_ids' => array_values($difficultyIds),
        ])->saveQuietly();
    }

    public static function syncAllDifficultyIds(): void
    {
        foreach (static::query()->orderBy('node_id')->pluck('node_id')->all() as $nodeId) {
            static::syncDifficultyIdsForNode($nodeId);
        }
    }
}
