<?php

namespace Modules\Notifications\Enums;

enum PostStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case PUBLISHED = 'published';

    
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
            self::DRAFT => __('enums.post_status.draft'),
            self::SCHEDULED => __('enums.post_status.scheduled'),
            self::PUBLISHED => __('enums.post_status.published'),
        };
    }
}
