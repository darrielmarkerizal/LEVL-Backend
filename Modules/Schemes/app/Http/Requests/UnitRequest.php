<?php

namespace Modules\Schemes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Schemes\Http\Requests\Concerns\HasApiValidation;
use Modules\Schemes\Http\Requests\Concerns\HasSchemesRequestRules;

class UnitRequest extends FormRequest
{
    use HasApiValidation, HasSchemesRequestRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $courseId = (int) $this->route('course');
        $unitId = $this->route('unit') ? (int) $this->route('unit') : 0;

        return $this->rulesUnit($courseId, $unitId);
    }

    public function messages(): array
    {
        return $this->messagesUnit();
    }
}
