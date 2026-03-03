<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Learning\Enums\AssignmentStatus;
use Modules\Learning\Enums\AssignmentType;
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
            'type' => ['required', Rule::enum(AssignmentType::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'order' => ['nullable', 'integer', 'min:1'],
            'submission_type' => ['required', Rule::enum(SubmissionType::class)],
            'max_score' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'passing_grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', Rule::enum(AssignmentStatus::class)],
            'allow_resubmit' => ['nullable', 'boolean'],
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');

            if ($type === 'assignment') {
                $submissionType = $this->input('submission_type');
                if ($submissionType && ! in_array($submissionType, ['file', 'mixed'])) {
                    $validator->errors()->add('submission_type', 'Assignment type must use file or mixed submission type.');
                }

                $reviewMode = $this->input('review_mode');
                if ($reviewMode && $reviewMode !== 'manual') {
                    $validator->errors()->add('review_mode', 'Assignment type must use manual review mode.');
                }

                if ($this->has('randomization_type')) {
                    $validator->errors()->add('randomization_type', 'Assignment type cannot have randomization type.');
                }

                if ($this->has('question_bank_count')) {
                    $validator->errors()->add('question_bank_count', 'Assignment type cannot have question bank count.');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'type' => __('validation.attributes.type'),
            'title' => __('validation.attributes.title'),
            'description' => __('validation.attributes.description'),
            'unit_id' => __('validation.attributes.unit_id'),
            'order' => __('validation.attributes.order'),
            'submission_type' => __('validation.attributes.submission_type'),
            'max_score' => __('validation.attributes.max_score'),
            'passing_grade' => __('validation.attributes.passing_grade'),
            'status' => __('validation.attributes.status'),
            'allow_resubmit' => __('validation.attributes.allow_resubmit'),
            'max_attempts' => __('validation.attributes.max_attempts'),
            'cooldown_minutes' => __('validation.attributes.cooldown_minutes'),
            'retake_enabled' => __('validation.attributes.retake_enabled'),
            'review_mode' => __('validation.attributes.review_mode'),
            'randomization_type' => __('validation.attributes.randomization_type'),
            'question_bank_count' => __('validation.attributes.question_bank_count'),
            'attachments' => __('validation.attributes.attachments'),
            'attachments.*' => __('validation.attributes.attachments'),
        ];
    }
}
