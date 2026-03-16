<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MainlineDifficulty extends Model
{
    protected $fillable = [
        'difficulty_id',
        'node_id',
        'difficulty_order',
        'difficulty_name',
        'recommended_power',
        'first_clear_reward_group_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'difficulty_order' => 'integer',
            'recommended_power' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $difficulty): void {
            $difficulty->syncNodeDifficultyIds();
        });

        static::deleted(function (self $difficulty): void {
            MainlineNode::syncDifficultyIdsForNode($difficulty->node_id);
        });
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(MainlineNode::class, 'node_id', 'node_id');
    }

    public static function defaultDifficultyOrder(string $difficultyId): int
    {
        return match ($difficultyId) {
            'easy' => 1,
            'normal' => 2,
            'hard' => 3,
            'nightmare' => 4,
            'epic' => 5,
            default => 99,
        };
    }

    public static function defaultDifficultyName(string $difficultyId): string
    {
        return match ($difficultyId) {
            'easy' => '简单',
            'normal' => '普通',
            'hard' => '困难',
            'nightmare' => '梦魇',
            'epic' => '史诗',
            default => $difficultyId,
        };
    }

    private function syncNodeDifficultyIds(): void
    {
        MainlineNode::syncDifficultyIdsForNode($this->node_id);

        if ($this->wasChanged('node_id')) {
            $originalNodeId = (string) $this->getOriginal('node_id');

            if ($originalNodeId !== '' && $originalNodeId !== $this->node_id) {
                MainlineNode::syncDifficultyIdsForNode($originalNodeId);
            }
        }
    }
}
