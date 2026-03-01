<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveQuizAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quiz_question_id' => ['required', 'integer', 'exists:quiz_questions,id'],
            'content' => ['nullable', 'string'],
            'selected_options' => ['nullable', 'array'],
            'selected_options.*' => ['string'],
        ];
    }
}
