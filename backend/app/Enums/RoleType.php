<?php

namespace App\Enums;

enum RoleType: string
{
    case Melee = 'melee';
    case Ranged = 'ranged';
    case Magic = 'magic';

    public function label(): string
    {
        return match ($this) {
            self::Melee => '近战',
            self::Ranged => '远程',
            self::Magic => '法术',
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
