<?php

namespace Modules\Notifications\Enums;

enum PostStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case PUBLISHED = 'published';

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
            self::DRAFT => __('enums.post_status.draft'),
            self::SCHEDULED => __('enums.post_status.scheduled'),
            self::PUBLISHED => __('enums.post_status.published'),
        };
    }
}
