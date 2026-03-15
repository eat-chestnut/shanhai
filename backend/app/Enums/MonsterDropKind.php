<?php

namespace App\Enums;

enum MonsterDropKind: string
{
    case Normal = 'normal';
    case BossFixed = 'boss_fixed';
    case BossCore = 'boss_core';

    public function label(): string
    {
        return match ($this) {
            self::Normal => '普通掉落',
            self::BossFixed => 'Boss 固定掉落',
            self::BossCore => 'Boss 核心掉落',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $case): string => $case->value,
            self::cases(),
        );
    }

    public static function infer(bool $isBoss, string $itemId, float $dropRate): self
    {
        if (! $isBoss) {
            return self::Normal;
        }

        if (str_contains(strtolower($itemId), 'core')) {
            return self::BossCore;
        }

        if ($dropRate >= 1) {
            return self::BossFixed;
        }

        return self::Normal;
    }
}
