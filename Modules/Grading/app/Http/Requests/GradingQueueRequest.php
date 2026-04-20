<?php

declare(strict_types=1);

namespace Modules\Grading\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradingQueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filter' => ['nullable', 'array'],
            'filter.status' => ['nullable', 'string'],
            'filter.workflow_state' => ['nullable', 'string'],
            'filter.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'filter.course_slug' => ['nullable', 'string', 'exists:courses,slug'],
            'filter.assignment_id' => ['nullable', 'integer', 'exists:assignments,id'],
            'filter.quiz_id' => ['nullable', 'integer', 'exists:quizzes,id'],
            'filter.grading_status' => ['nullable', 'string'],
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
            'filter.grading_status' => __('validation.attributes.grading_status'),
            'filter.date_from' => __('validation.attributes.date_from'),
            'filter.date_to' => __('validation.attributes.date_to'),
        ];
    }
}
