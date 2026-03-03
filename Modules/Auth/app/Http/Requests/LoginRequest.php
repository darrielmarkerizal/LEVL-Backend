<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Auth\Http\Requests\Concerns\HasAuthRequestRules;
use Modules\Common\Http\Requests\Concerns\HasApiValidation;

class LoginRequest extends FormRequest
{
    use HasApiValidation, HasAuthRequestRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->rulesLogin();
    }

    public function messages(): array
    {
        return $this->messagesLogin();
    }
}
