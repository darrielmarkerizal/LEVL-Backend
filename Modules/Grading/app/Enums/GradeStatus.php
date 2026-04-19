<?php

namespace Modules\Grading\Enums;

enum GradeStatus: string
{
    case Pending = 'pending';
    case Graded = 'graded';
    case Reviewed = 'reviewed';

    
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
            self::Pending => __('enums.grade_status.pending'),
            self::Graded => __('enums.grade_status.graded'),
            self::Reviewed => __('enums.grade_status.reviewed'),
        };
    }
}
