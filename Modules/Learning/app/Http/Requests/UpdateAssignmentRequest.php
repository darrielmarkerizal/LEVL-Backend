<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Learning\Enums\AssignmentStatus;
use Modules\Learning\Enums\SubmissionType;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit_slug' => ['sometimes', 'string', 'exists:units,slug'],
            'order' => ['sometimes', 'integer', 'min:1'],
            'submission_type' => ['sometimes', Rule::enum(SubmissionType::class)],
            'max_score' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'passing_grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['sometimes', Rule::enum(AssignmentStatus::class)],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'],
            'delete_attachments' => ['nullable', 'array'],
            'delete_attachments.*' => ['integer', 'exists:media,id'],
        ];
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
            'delete_attachments' => __('validation.attributes.delete_attachments'),
            'delete_attachments.*' => __('validation.attributes.delete_attachments'),
        ];
    }
}
