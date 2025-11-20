<?php

namespace Modules\Assessments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Common\Http\Requests\Concerns\HasApiValidation;
use Modules\Assessments\Http\Requests\Concerns\HasAssessmentValidationRules;
use Modules\Assessments\Http\Requests\Concerns\HasCommonValidationMessages;

class QuestionStoreRequest extends FormRequest
{
    use HasApiValidation, HasAssessmentValidationRules, HasCommonValidationMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->rulesQuestionStore();
    }

    public function messages(): array
    {
        return $this->messagesQuestionStore();
    }
}
