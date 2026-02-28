<?php

declare(strict_types=1);

namespace Modules\Learning\Enums;

enum AssignmentType: string
{
    case Assignment = 'assignment';
    case Quiz = 'quiz';

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
            self::Assignment => __('enums.assignment_type.assignment'),
            self::Quiz => __('enums.assignment_type.quiz'),
        };
    }

    public function isAssignment(): bool
    {
        return $this === self::Assignment;
    }

    public function isQuiz(): bool
    {
        return $this === self::Quiz;
    }
}
