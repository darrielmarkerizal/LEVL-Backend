<?php

declare(strict_types=1);

namespace Modules\Learning\Enums;

enum QuizQuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case Checkbox = 'checkbox';
    case TrueFalse = 'true_false';
    case Essay = 'essay';

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
            self::MultipleChoice => __('enums.quiz_question_type.multiple_choice'),
            self::Checkbox => __('enums.quiz_question_type.checkbox'),
            self::TrueFalse => __('enums.quiz_question_type.true_false'),
            self::Essay => __('enums.quiz_question_type.essay'),
        };
    }

    public function canAutoGrade(): bool
    {
        return match ($this) {
            self::MultipleChoice, self::Checkbox, self::TrueFalse => true,
            self::Essay => false,
        };
    }

    public function requiresOptions(): bool
    {
        return match ($this) {
            self::MultipleChoice, self::Checkbox, self::TrueFalse => true,
            self::Essay => false,
        };
    }

    public function isObjective(): bool
    {
        return $this->canAutoGrade();
    }
}
