<?php

namespace Modules\Content\Enums;

enum Priority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';

    
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
            self::Low => __('enums.priority.low'),
            self::Normal => __('enums.priority.normal'),
            self::High => __('enums.priority.high'),
        };
    }
}
