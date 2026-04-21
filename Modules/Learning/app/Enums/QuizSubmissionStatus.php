<?php

declare(strict_types=1);

namespace Modules\Learning\Enums;

enum QuizSubmissionStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Graded = 'graded';
    case Released = 'released';
    case Missing = 'missing';

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
            self::Draft => __('enums.quiz_submission_status.draft'),
            self::Submitted => __('enums.quiz_submission_status.submitted'),
            self::Graded => __('enums.quiz_submission_status.graded'),
            self::Released => __('enums.quiz_submission_status.released'),
            self::Missing => __('enums.quiz_submission_status.missing'),
        };
    }
}
