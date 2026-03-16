<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scripture extends Model
{
    protected $primaryKey = 'scripture_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'scripture_id',
        'scripture_name',
        'scripture_group',
        'sort_order',
        'unlock_condition',
        'is_enabled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'unlock_condition' => 'array',
            'is_enabled' => 'boolean',
        ];
    }

    public function bindings(): HasMany
    {
        return $this->hasMany(ScriptureChapterBinding::class, 'scripture_id', 'scripture_id');
    }

    public function worldTiers(): HasMany
    {
        return $this->hasMany(ScriptureWorldTier::class, 'scripture_id', 'scripture_id');
    }

    public function upgradeCosts(): HasMany
    {
        return $this->hasMany(ScriptureUpgradeCost::class, 'scripture_id', 'scripture_id');
    }

    public static function getEnabledScriptureOptions(): array
    {
        return static::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('scripture_id')
            ->get()
            ->mapWithKeys(static fn (self $scripture): array => [
                $scripture->scripture_id => "{$scripture->scripture_id} / {$scripture->scripture_name}",
            ])
            ->all();
    }
}
