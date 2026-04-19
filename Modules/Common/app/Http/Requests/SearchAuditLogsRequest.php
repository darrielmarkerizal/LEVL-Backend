<?php

declare(strict_types=1);

namespace Modules\Common\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Common\Services\AssessmentAuditService;


class SearchAuditLogsRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true; 
    }

    
    public function rules(): array
    {
        $validActions = [
            AssessmentAuditService::ACTION_SUBMISSION_CREATED,
            AssessmentAuditService::ACTION_STATE_TRANSITION,
            AssessmentAuditService::ACTION_GRADING,
            AssessmentAuditService::ACTION_ANSWER_KEY_CHANGE,
            AssessmentAuditService::ACTION_GRADE_OVERRIDE,

            AssessmentAuditService::ACTION_OVERRIDE_GRANT,
        ];

        return [
            
            'filter.action' => ['nullable', 'string', 'in:'.implode(',', $validActions)],
            'filter.actions' => ['nullable', 'string'], 
            'filter.actor_id' => ['nullable', 'integer', 'exists:users,id'],
            'filter.actor_type' => ['nullable', 'string'],
            'filter.subject_id' => ['nullable', 'integer'],
            'filter.subject_type' => ['nullable', 'string'],
            'filter.created_between' => ['nullable', 'string'], 
            'filter.context_contains' => ['nullable', 'string', 'max:255'],
            'filter.assignment_id' => ['nullable', 'integer', 'exists:assignments,id'],
            'filter.student_id' => ['nullable', 'integer', 'exists:users,id'],

            
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort' => ['nullable', 'string'],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }

    
    public function attributes(): array
    {
        return [
            'action' => __('validation.attributes.action'),
            'actions' => __('validation.attributes.actions'),
            'actor_id' => __('validation.attributes.actor'),
            'actor_type' => __('validation.attributes.actor_type'),
            'subject_id' => __('validation.attributes.subject'),
            'subject_type' => __('validation.attributes.subject_type'),
            'start_date' => __('validation.attributes.start_date'),
            'end_date' => __('validation.attributes.end_date'),
            'context_search' => __('validation.attributes.search'),
            'assignment_id' => __('validation.attributes.assignment'),
            'student_id' => __('validation.attributes.student'),
        ];
    }

    
    public function messages(): array
    {
        return [
            'action.in' => __('validation.in', ['attribute' => 'action']),
            'actions.*.in' => __('validation.in', ['attribute' => 'action']),
            'start_date.before_or_equal' => __('validation.before_or_equal', [
                'attribute' => 'start date',
                'date' => 'end date',
            ]),
            'end_date.after_or_equal' => __('validation.after_or_equal', [
                'attribute' => 'end date',
                'date' => 'start date',
            ]),
        ];
    }
}
