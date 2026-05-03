<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Learning\Enums\QuizQuestionType;
use Modules\Learning\Http\Support\TrueFalseAnswerKeyNormalizer;

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
            'options' => ['nullable', 'array', 'min:2'],
            'options.*.text' => ['nullable', 'string'],
            'options.*.image' => ['nullable', 'file', 'image'],
            // true_false uses [0] or [1]; others use array of option indices
            'answer_key' => ['nullable'],
            'answer_key.*' => ['integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0.01'],
            'order' => ['nullable', 'integer', 'min:0'],
            'max_score' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function prepareForValidation(): void
    {
        $type = $this->input('type');
        $answerKey = $this->input('answer_key');

        // Wrap single value answer_key into array for multiple_choice (handles string "1" from form-data)
        if ($type === QuizQuestionType::MultipleChoice->value && ! is_array($answerKey) && $answerKey !== null) {
            $this->merge(['answer_key' => [(int) $answerKey]]);
        }

        if ($type === QuizQuestionType::TrueFalse->value && $answerKey !== null) {
            $normalized = TrueFalseAnswerKeyNormalizer::normalize($answerKey);
            if ($normalized !== null) {
                $this->merge(['answer_key' => $normalized]);
            }
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');
            if (! $type) {
                return;
            }

            try {
                $questionType = QuizQuestionType::from($type);
            } catch (\ValueError) {
                return;
            }

            // Options must have at least 2 items for types that require options
            if ($questionType->requiresOptions()) {
                $options = $this->input('options', []);
                if (count($options) < 2) {
                    $validator->errors()->add('options', __('messages.questions.options_min_two'));
                }
            }

            // answer_key required for auto-gradable types
            if ($questionType->canAutoGrade()) {
                $answerKey = $this->input('answer_key');

                if ($questionType === QuizQuestionType::TrueFalse) {
                    if (
                        ! is_array($answerKey)
                        || count($answerKey) !== 1
                        || ! in_array((int) ($answerKey[0] ?? -1), [0, 1], true)
                    ) {
                        $validator->errors()->add('answer_key', __('messages.questions.answer_key_required'));
                    }
                } else {
                    if (empty($answerKey)) {
                        $validator->errors()->add('answer_key', __('messages.questions.answer_key_required'));
                    } elseif (! is_array($answerKey)) {
                        $validator->errors()->add('answer_key', __('messages.questions.answer_key_required'));
                    } else {
                        // answer_key indices must be valid option indices
                        $options = $this->input('options', []);
                        $optionCount = count($options);
                        foreach ($answerKey as $idx => $keyIndex) {
                            if ((int) $keyIndex >= $optionCount) {
                                $validator->errors()->add(
                                    "answer_key.{$idx}",
                                    __('messages.questions.answer_key_out_of_range', [
                                        'index' => $keyIndex,
                                        'max' => $optionCount - 1,
                                    ])
                                );
                            }
                        }
                    }
                }
            }

            // max_score not applicable for auto-graded types — silently ignored
            // (max_score will be set equal to weight automatically)
        });
    }
}
