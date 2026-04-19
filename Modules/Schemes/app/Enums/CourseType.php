<?php

declare(strict_types=1);

namespace Modules\Schemes\Enums;

enum CourseType: string
{
    case Okupasi = 'okupasi';
    case Kluster = 'kluster';

    
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
            self::Okupasi => __('enums.course_type.okupasi'),
            self::Kluster => __('enums.course_type.kluster'),
        };
    }
}
