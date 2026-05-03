<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Learning\Enums\QuizQuestionType;
use Modules\Learning\Http\Support\TrueFalseAnswerKeyNormalizer;
use Modules\Learning\Models\QuizQuestion;

class UpdateQuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', QuizQuestionType::rule()],
            'content' => ['sometimes', 'string'],
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

        $isTrueFalse = $type === QuizQuestionType::TrueFalse->value;
        if (! $isTrueFalse && $type === null) {
            $question = $this->route('question');
            if ($question instanceof QuizQuestion) {
                $isTrueFalse = $question->type === QuizQuestionType::TrueFalse;
            }
        }

        if ($isTrueFalse && $answerKey !== null) {
            $normalized = TrueFalseAnswerKeyNormalizer::normalize($answerKey);
            if ($normalized !== null) {
                $this->merge(['answer_key' => $normalized]);
            }
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $typeInput = $this->input('type');

            // Resolve type: from input or from existing question via route
            if ($typeInput) {
                try {
                    $questionType = QuizQuestionType::from($typeInput);
                } catch (\ValueError) {
                    return;
                }
            } else {
                $question = $this->route('question');
                if (! $question) {
                    return;
                }
                $questionType = $question->type instanceof QuizQuestionType
                    ? $question->type
                    : QuizQuestionType::from($question->type);
            }

            // Resolve options: from input or from existing question
            $options = $this->input('options');
            if ($options === null) {
                $question = $question ?? $this->route('question');
                $options = $question?->options ?? [];
            }
            $optionCount = count($options);

            // answer_key indices must be valid
            if ($this->has('answer_key') && $questionType->canAutoGrade()) {
                if ($questionType === QuizQuestionType::TrueFalse) {
                    $answerKey = $this->input('answer_key');
                    if (
                        ! is_array($answerKey)
                        || count($answerKey) !== 1
                        || ! in_array((int) ($answerKey[0] ?? -1), [0, 1], true)
                    ) {
                        $validator->errors()->add('answer_key', __('messages.questions.answer_key_required'));
                    }
                } else {
                    $answerKey = $this->input('answer_key', []);
                    if (is_array($answerKey)) {
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
