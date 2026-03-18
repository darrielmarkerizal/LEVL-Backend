<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Learning\Enums\AssignmentStatus;
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
            'unit_slug' => ['required', 'string', 'exists:units,slug'],
            'order' => ['nullable', 'integer', 'min:1'],
            'submission_type' => ['required', Rule::enum(SubmissionType::class)],
            'max_score' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'passing_grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', Rule::enum(AssignmentStatus::class)],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $submissionType = $this->input('submission_type');
            if ($submissionType && ! in_array($submissionType, ['file', 'mixed'])) {
                $validator->errors()->add('submission_type', 'Assignment must use file or mixed submission type.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'title' => __('validation.attributes.title'),
            'description' => __('validation.attributes.description'),
            'unit_slug' => __('validation.attributes.unit_slug'),
            'order' => __('validation.attributes.order'),
            'submission_type' => __('validation.attributes.submission_type'),
            'max_score' => __('validation.attributes.max_score'),
            'passing_grade' => __('validation.attributes.passing_grade'),
            'status' => __('validation.attributes.status'),
            'attachments' => __('validation.attributes.attachments'),
            'attachments.*' => __('validation.attributes.attachments'),
        ];
    }
}
