<?php

namespace App\Enums;

enum SkillType: string
{
    case Active = 'active';
    case Passive = 'passive';

    public function label(): string
    {
        return match ($this) {
            self::Active => '主动技能',
            self::Passive => '被动技能',
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
}
