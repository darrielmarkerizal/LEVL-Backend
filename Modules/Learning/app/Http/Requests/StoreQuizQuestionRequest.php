<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Learning\Enums\QuizQuestionType;

class StoreQuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', QuizQuestionType::rule()],
            'content' => ['required', 'string'],
            'options' => ['nullable', 'array'],
            'options.*.text' => ['nullable', 'string'],
            'options.*.image' => ['nullable', 'file', 'image'],
            'answer_key' => ['nullable', 'array'],
            'weight' => ['nullable', 'numeric', 'min:0.01'],
            'order' => ['nullable', 'integer', 'min:0'],
            'max_score' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
