<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MainlineDifficulty extends Model
{
    protected $fillable = [
        'difficulty_id',
        'node_id',
        'recommended_power',
        'first_clear_reward_group_id',
    ];

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
