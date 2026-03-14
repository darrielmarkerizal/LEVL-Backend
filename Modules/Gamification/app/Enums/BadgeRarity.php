<?php

namespace Modules\Gamification\Enums;

enum BadgeRarity: string
{
    case Common = 'common';
    case Uncommon = 'uncommon';
    case Rare = 'rare';
    case Epic = 'epic';
    case Legendary = 'legendary';

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
            self::Common => __('enums.badge_rarity.common'),
            self::Uncommon => __('enums.badge_rarity.uncommon'),
            self::Rare => __('enums.badge_rarity.rare'),
            self::Epic => __('enums.badge_rarity.epic'),
            self::Legendary => __('enums.badge_rarity.legendary'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Common => '#9CA3AF',      // Gray
            self::Uncommon => '#10B981',    // Green
            self::Rare => '#3B82F6',        // Blue
            self::Epic => '#8B5CF6',        // Purple
            self::Legendary => '#F59E0B',   // Gold
        };
    }
}
