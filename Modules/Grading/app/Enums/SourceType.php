<?php

namespace Modules\Grading\Enums;

enum SourceType: string
{
    case Assignment = 'assignment';
    case Attempt = 'attempt';

    
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
            self::Assignment => __('enums.source_type.assignment'),
            self::Attempt => __('enums.source_type.attempt'),
        };
    }
}
