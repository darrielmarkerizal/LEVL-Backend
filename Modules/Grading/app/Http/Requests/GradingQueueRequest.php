<?php

declare(strict_types=1);

namespace Modules\Grading\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Learning\Enums\QuizGradingStatus;
use Modules\Learning\Enums\QuizSubmissionStatus;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Enums\SubmissionStatus;

class GradingQueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $workflowValues = array_values(array_unique(array_merge(
            SubmissionState::values(),
            QuizGradingStatus::values()
        )));

        $statusValues = array_values(array_unique(array_merge(
            SubmissionStatus::values(),
            QuizSubmissionStatus::values()
        )));

        return [
            'filter' => ['nullable', 'array'],
            'filter.status' => ['nullable', 'string', Rule::in($statusValues)],
            'filter.workflow_state' => ['nullable', 'string', Rule::in($workflowValues)],
            'filter.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'filter.course_slug' => ['nullable', 'string', 'exists:courses,slug'],
            'filter.assignment_id' => ['nullable', 'integer', 'exists:assignments,id'],
            'filter.quiz_id' => ['nullable', 'integer', 'exists:quizzes,id'],
            'filter.question_id' => ['nullable', 'integer', 'exists:quiz_questions,id'],
            'filter.grading_status' => ['nullable', 'string', Rule::in(QuizGradingStatus::values())],
            'filter.date_from' => ['nullable', 'date', 'before_or_equal:filter.date_to'],
            'filter.date_to' => ['nullable', 'date', 'after_or_equal:filter.date_from'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'filter.status' => __('validation.attributes.status'),
            'filter.workflow_state' => __('validation.attributes.workflow_state'),
            'filter.user_id' => __('validation.attributes.student'),
            'filter.course_slug' => __('validation.attributes.course'),
            'filter.assignment_id' => __('validation.attributes.assignment'),
            'filter.quiz_id' => __('validation.attributes.quiz'),
            'filter.question_id' => __('validation.attributes.question_id'),
            'filter.grading_status' => __('validation.attributes.grading_status'),
            'filter.date_from' => __('validation.attributes.date_from'),
            'filter.date_to' => __('validation.attributes.date_to'),
        ];
    }
}
