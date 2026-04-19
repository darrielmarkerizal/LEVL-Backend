<?php

declare(strict_types=1);

namespace Modules\Schemes\Enums;

enum EnrollmentType: string
{
    case AutoAccept = 'auto_accept';
    case KeyBased = 'key_based';
    case Approval = 'approval';

    
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
            self::AutoAccept => __('enums.enrollment_type.auto_accept'),
            self::KeyBased => __('enums.enrollment_type.key_based'),
            self::Approval => __('enums.enrollment_type.approval'),
        };
    }
}
