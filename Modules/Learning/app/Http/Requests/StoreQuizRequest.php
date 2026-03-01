<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assignable_type' => ['required', 'string', 'in:lesson,unit,course'],
            'assignable_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'passing_grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'auto_grading' => ['nullable', 'boolean'],
            'max_score' => ['nullable', 'numeric', 'min:1'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'cooldown_minutes' => ['nullable', 'integer', 'min:0'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'retake_enabled' => ['nullable', 'boolean'],
            'randomization_type' => ['nullable', 'string', 'in:static,random_order,bank'],
            'question_bank_count' => ['nullable', 'integer', 'min:1'],
            'review_mode' => ['nullable', 'string', 'in:immediate,after_deadline,never'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file'],
        ];
    }

    public function getResolvedScope(): array
    {
        $typeMap = [
            'lesson' => \Modules\Schemes\Models\Lesson::class,
            'unit' => \Modules\Schemes\Models\Unit::class,
            'course' => \Modules\Schemes\Models\Course::class,
        ];

        $type = $this->validated('assignable_type');

        return [
            'assignable_type' => $typeMap[$type] ?? $type,
            'assignable_id' => $this->validated('assignable_id'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $typeMap = [
            'lesson' => \Modules\Schemes\Models\Lesson::class,
            'unit' => \Modules\Schemes\Models\Unit::class,
            'course' => \Modules\Schemes\Models\Course::class,
        ];

        if ($this->has('assignable_type') && isset($typeMap[$this->assignable_type])) {
            $this->merge(['_assignable_class' => $typeMap[$this->assignable_type]]);
        }
    }
}
