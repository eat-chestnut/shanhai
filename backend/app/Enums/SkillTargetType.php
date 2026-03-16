<?php

namespace App\Enums;

enum SkillTargetType: string
{
    case Single = 'single';
    case Multi = 'multi';
    case Area = 'area';
    case SelfTarget = 'self';
    case Passive = 'passive';

    public function label(): string
    {
        return match ($this) {
            self::Single => '单体',
            self::Multi => '多目标',
            self::Area => '范围',
            self::SelfTarget => '自身',
            self::Passive => '被动',
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
