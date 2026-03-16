<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentSet extends Model
{
    private const EFFECT_INT_FIELDS = [
        'bonus_atk',
        'bonus_def',
        'bonus_hp',
        'bonus_boss_dmg',
    ];

    private const EFFECT_FLOAT_FIELDS = [
        'bonus_attack_speed',
        'bonus_damage_ratio',
    ];

    protected $fillable = [
        'set_id',
        'level',
        'pieces',
        'effects',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pieces' => 'array',
            'effects' => 'array',
        ];
    }

    /**
     * @return list<array<string, int|float>>
     */
    public function normalizedEffects(): array
    {
        return self::normalizeEffectsPayload($this->effects);
    }

    /**
     * @return list<array<string, int|float>>
     */
    public static function normalizeEffectsPayload(mixed $effects): array
    {
        if (! is_array($effects) || $effects === []) {
            return [];
        }

        $normalized = [];

        if (array_is_list($effects)) {
            foreach ($effects as $effect) {
                $entry = self::normalizeEffectEntry($effect);

                if ($entry !== null) {
                    $normalized[] = $entry;
                }
            }

            return $normalized;
        }

        if (self::looksLikeEffectEntry($effects)) {
            $entry = self::normalizeEffectEntry($effects);

            return $entry !== null ? [$entry] : [];
        }

        foreach ($effects as $count => $effect) {
            $entry = self::normalizeEffectEntry($effect, $count);

            if ($entry !== null) {
                $normalized[] = $entry;
            }
        }

        return $normalized;
    }

    /**
     * @param  mixed  $effect
     * @return array<string, int|float>|null
     */
    private static function normalizeEffectEntry(mixed $effect, int|string|null $countKey = null): ?array
    {
        if (! is_array($effect)) {
            return null;
        }

        $count = $effect['count'] ?? null;

        if (($count === null || $count === '') && $countKey !== null && is_numeric((string) $countKey)) {
            $count = (int) $countKey;
        }

        $normalized = [
            'count' => (int) ($count ?? 0),
        ];

        foreach (self::EFFECT_INT_FIELDS as $field) {
            $value = $effect[$field] ?? null;

            if ($value !== null && $value !== '') {
                $normalized[$field] = (int) $value;
            }
        }

        foreach (self::EFFECT_FLOAT_FIELDS as $field) {
            $value = $effect[$field] ?? null;

            if ($value !== null && $value !== '') {
                $normalized[$field] = (float) $value;
            }
        }

        return count($normalized) > 1 ? $normalized : null;
    }

    /**
     * @param  array<string, mixed>  $effect
     */
    private static function looksLikeEffectEntry(array $effect): bool
    {
        if (array_key_exists('count', $effect)) {
            return true;
        }

        foreach ([...self::EFFECT_INT_FIELDS, ...self::EFFECT_FLOAT_FIELDS] as $field) {
            if (array_key_exists($field, $effect)) {
                return true;
            }
        }

        return false;
    }
}
