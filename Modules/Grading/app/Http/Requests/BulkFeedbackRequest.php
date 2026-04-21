<?php

declare(strict_types=1);

namespace Modules\Grading\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'submission_ids' => ['nullable', 'array', 'min:1'],
            'submission_ids.*' => ['required', 'integer', 'exists:submissions,id'],
            'targets' => ['nullable', 'array', 'min:1'],
            'targets.*.type' => ['required_with:targets', 'string', 'in:assignment,quiz'],
            'targets.*.submission_id' => ['required_with:targets', 'integer', 'min:1'],
            'targets.*.question_id' => ['nullable', 'integer', 'exists:quiz_questions,id'],
            'feedback' => ['required', 'string', 'min:1'],
            'async' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasLegacyIds = ! empty($this->input('submission_ids'));
            $hasTargets = ! empty($this->input('targets'));

            if (! $hasLegacyIds && ! $hasTargets) {
                $validator->errors()->add('targets', 'Either submission_ids or targets must be provided.');
            }

            if ($hasLegacyIds && $hasTargets) {
                $validator->errors()->add('targets', 'Use either submission_ids or targets, not both.');
            }

            foreach ((array) $this->input('targets', []) as $index => $target) {
                if (($target['type'] ?? null) === 'quiz' && empty($target['question_id'])) {
                    $validator->errors()->add("targets.{$index}.question_id", 'question_id is required for quiz targets.');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'submission_ids' => __('validation.attributes.submissions'),
            'submission_ids.*' => __('validation.attributes.submission'),
            'targets' => __('validation.attributes.submissions'),
            'targets.*.type' => __('validation.attributes.type'),
            'targets.*.submission_id' => __('validation.attributes.submission'),
            'targets.*.question_id' => __('validation.attributes.question_id'),
            'feedback' => __('validation.attributes.feedback'),
            'async' => __('validation.attributes.async'),
        ];
    }

    public function messages(): array
    {
        return [
            'submission_ids.required' => 'At least one submission must be selected.',
            'submission_ids.min' => 'At least one submission must be selected.',
            'feedback.required' => 'Feedback text is required.',
        ];
    }
}
