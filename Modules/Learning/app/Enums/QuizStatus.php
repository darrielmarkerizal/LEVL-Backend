<?php

declare(strict_types=1);

namespace Modules\Learning\Enums;

enum QuizStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

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
            self::Draft => __('enums.quiz_status.draft'),
            self::Published => __('enums.quiz_status.published'),
            self::Archived => __('enums.quiz_status.archived'),
        };
    }
}
