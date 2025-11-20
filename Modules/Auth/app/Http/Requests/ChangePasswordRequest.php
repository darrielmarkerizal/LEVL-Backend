<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Common\Http\Requests\Concerns\HasApiValidation;
use Modules\Auth\Http\Requests\Concerns\HasAuthRequestRules;
use Modules\Auth\Http\Requests\Concerns\HasPasswordRules;

class ChangePasswordRequest extends FormRequest
{
    use HasApiValidation, HasAuthRequestRules, HasPasswordRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->rulesChangePassword();
    }

    public function messages(): array
    {
        return $this->messagesChangePassword();
    }
}
