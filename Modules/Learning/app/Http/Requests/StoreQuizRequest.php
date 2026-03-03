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
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'order' => ['nullable', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'passing_grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'auto_grading' => ['nullable', 'boolean'],
            'max_score' => ['nullable', 'numeric', 'min:1'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'randomization_type' => ['nullable', 'string', 'in:static,random_order,bank'],
            'question_bank_count' => ['nullable', 'integer', 'min:1'],
            'review_mode' => ['nullable', 'string', 'in:immediate,after_deadline,never'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file'],
        ];
    }
}
