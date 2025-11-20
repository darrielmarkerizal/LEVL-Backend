<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Common\Http\Requests\Concerns\HasApiValidation;
use Modules\Auth\Http\Requests\Concerns\HasAuthRequestRules;

class RequestEmailChangeRequest extends FormRequest
{
    use HasApiValidation, HasAuthRequestRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->rulesRequestEmailChange();
    }

    public function messages(): array
    {
        return $this->messagesRequestEmailChange();
    }
}
