<?php

namespace App\Enums;

enum SkillEffectType: string
{
    case Damage = 'damage';
    case Dot = 'dot';
    case Hot = 'hot';
    case Control = 'control';
    case Attribute = 'attribute';
    case Trigger = 'trigger';

    public function label(): string
    {
        return match ($this) {
            self::Damage => '直接伤害',
            self::Dot => '持续伤害',
            self::Hot => '持续治疗',
            self::Control => '控制效果',
            self::Attribute => '属性加成',
            self::Trigger => '触发效果',
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
