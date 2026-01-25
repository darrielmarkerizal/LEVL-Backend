<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Learning\Enums\AssignmentStatus;
use Modules\Learning\Enums\RandomizationType;
use Modules\Learning\Enums\ReviewMode;
use Modules\Learning\Enums\SubmissionType;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assignable_type' => [
                'required',
                'string',
                Rule::in(['Course', 'Unit', 'Lesson']),
            ],
            'assignable_slug' => ['required', 'string'],
            'assignable_id' => ['nullable', 'integer'],
            'submission_type' => ['required', Rule::enum(SubmissionType::class)],
            'max_score' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'available_from' => ['nullable', 'date', 'after:now'],
            'deadline_at' => ['nullable', 'date', 'after_or_equal:available_from'],
            'status' => ['nullable', Rule::enum(AssignmentStatus::class)],
            'allow_resubmit' => ['nullable', 'boolean'],
            'late_penalty_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'tolerance_minutes' => ['nullable', 'integer', 'min:0'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'cooldown_minutes' => ['nullable', 'integer', 'min:0'],
            'retake_enabled' => ['nullable', 'boolean'],
            'review_mode' => ['nullable', Rule::enum(ReviewMode::class)],
            'randomization_type' => ['nullable', Rule::enum(RandomizationType::class)],
            'question_bank_count' => ['nullable', 'integer', 'min:0'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    private ?array $resolvedScope = null;

    public function getResolvedScope(): ?array
    {
        return $this->resolvedScope;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $typeMap = [
                'Course' => 'Modules\\Schemes\\Models\\Course',
                'Unit' => 'Modules\\Schemes\\Models\\Unit',
                'Lesson' => 'Modules\\Schemes\\Models\\Lesson',
            ];

            $assignableType = $this->input('assignable_type');
            $assignableSlug = $this->input('assignable_slug');

            if ($assignableType && isset($typeMap[$assignableType]) && $assignableSlug) {
                $modelClass = $typeMap[$assignableType];
                
                $query = $modelClass::where('slug', $assignableSlug);
                
                if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass))) {
                    $query->whereNull('deleted_at');
                }
                
                $model = $query->first();
                
                if (!$model) {
                    $validator->errors()->add('assignable_slug', __('validation.exists', ['attribute' => 'assignable slug']));
                } else {
                    $this->resolvedScope = [
                        'assignable_id' => $model->id,
                        'assignable_type' => $modelClass,
                    ];
                    // Also merge for convenience if needed, but we rely on getter now
                    $this->merge($this->resolvedScope);
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'title' => __('validation.attributes.title'),
            'description' => __('validation.attributes.description'),
            'assignable_type' => __('validation.attributes.assignable_type'),
            'assignable_slug' => __('validation.attributes.assignable_slug'),
            'submission_type' => __('validation.attributes.submission_type'),
            'max_score' => __('validation.attributes.max_score'),
            'available_from' => __('validation.attributes.available_from'),
            'deadline_at' => __('validation.attributes.deadline_at'),
            'status' => __('validation.attributes.status'),
            'allow_resubmit' => __('validation.attributes.allow_resubmit'),
            'late_penalty_percent' => __('validation.attributes.late_penalty_percent'),
            'tolerance_minutes' => __('validation.attributes.tolerance_minutes'),
            'max_attempts' => __('validation.attributes.max_attempts'),
            'cooldown_minutes' => __('validation.attributes.cooldown_minutes'),
            'retake_enabled' => __('validation.attributes.retake_enabled'),
            'review_mode' => __('validation.attributes.review_mode'),
            'randomization_type' => __('validation.attributes.randomization_type'),
            'question_bank_count' => __('validation.attributes.question_bank_count'),
            'attachments' => __('validation.attributes.attachments'),
            'attachments.*' => __('validation.attributes.attachments'),
            'assignable_id' => __('validation.attributes.assignable_id'),
        ];
    }
}
