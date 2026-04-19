<?php

namespace Modules\Notifications\Enums;

enum PostAudienceRole: string
{
    case STUDENT = 'student';
    case INSTRUCTOR = 'instructor';
    case ADMIN = 'admin';

    
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
            self::STUDENT => __('enums.post_audience_role.student'),
            self::INSTRUCTOR => __('enums.post_audience_role.instructor'),
            self::ADMIN => __('enums.post_audience_role.admin'),
        };
    }
}
