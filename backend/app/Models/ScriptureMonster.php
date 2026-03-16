<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScriptureMonster extends Model
{
    protected $primaryKey = 'monster_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'monster_id',
        'name',
        'monster_type',
        'race',
        'rarity',
        'base_hp',
        'base_atk',
        'base_def',
        'move_speed',
        'ai_type',
        'skill_ids',
        'is_boss',
        'is_elite',
        'is_enabled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_hp' => 'integer',
            'base_atk' => 'integer',
            'base_def' => 'integer',
            'move_speed' => 'integer',
            'skill_ids' => 'array',
            'is_boss' => 'boolean',
            'is_elite' => 'boolean',
            'is_enabled' => 'boolean',
        ];
    }

    public static function getEnabledMonsterOptions(): array
    {
        return static::query()
            ->where('is_enabled', true)
            ->orderBy('monster_type')
            ->orderBy('monster_id')
            ->get()
            ->mapWithKeys(static fn (self $monster): array => [
                $monster->monster_id => "{$monster->monster_id} / {$monster->name}",
            ])
            ->all();
    }
}
