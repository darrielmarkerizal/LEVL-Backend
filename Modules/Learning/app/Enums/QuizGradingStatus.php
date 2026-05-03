<?php

declare(strict_types=1);

namespace Modules\Learning\Enums;

enum QuizGradingStatus: string
{
    case Pending = 'pending';
    case PartiallyGraded = 'partially_graded';
    case WaitingForGrading = 'waiting_for_grading';
    case Graded = 'graded';
    case Released = 'released';

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
            self::Pending => __('enums.quiz_grading_status.pending'),
            self::PartiallyGraded => __('enums.quiz_grading_status.partially_graded'),
            self::WaitingForGrading => __('enums.quiz_grading_status.waiting_for_grading'),
            self::Graded => __('enums.quiz_grading_status.graded'),
            self::Released => __('enums.quiz_grading_status.released'),
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Graded, self::Released], true);
    }

    public function needsManualGrading(): bool
    {
        return in_array($this, [self::PartiallyGraded, self::WaitingForGrading], true);
    }
}
