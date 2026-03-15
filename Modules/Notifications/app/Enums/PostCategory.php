<?php

namespace Modules\Notifications\Enums;

enum PostCategory: string
{
    case ANNOUNCEMENT = 'announcement';
    case INFORMATION = 'information';
    case WARNING = 'warning';
    case SYSTEM = 'system';
    case AWARD = 'award';
    case GAMIFICATION = 'gamification';

    /**
     * Get all enum values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get enum for validation rules.
     */
    public static function rule(): string
    {
        return 'in:'.implode(',', self::values());
    }

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ANNOUNCEMENT => __('enums.post_category.announcement'),
            self::INFORMATION => __('enums.post_category.information'),
            self::WARNING => __('enums.post_category.warning'),
            self::SYSTEM => __('enums.post_category.system'),
            self::AWARD => __('enums.post_category.award'),
            self::GAMIFICATION => __('enums.post_category.gamification'),
        };
    }
}
