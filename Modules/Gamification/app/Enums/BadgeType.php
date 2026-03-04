<?php

namespace Modules\Gamification\Enums;

enum BadgeType: string
{
    case Completion = 'completion';
    case Quality = 'quality';
    case Speed = 'speed';
    case Habit = 'habit';
    case Social = 'social';
    case Hidden = 'hidden';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function rule(): string
    {
        return 'in:'.implode(',', self::values());
    }

    public function label(): string
    {
        return match ($this) {
            self::Completion => __('enums.badge_type.completion'),
            self::Quality => __('enums.badge_type.quality'),
            self::Speed => __('enums.badge_type.speed'),
            self::Habit => __('enums.badge_type.habit'),
            self::Social => __('enums.badge_type.social'),
            self::Hidden => __('enums.badge_type.hidden'),
        };
    }
}
