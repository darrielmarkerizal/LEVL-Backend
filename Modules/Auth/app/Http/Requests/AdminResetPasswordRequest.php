<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Auth\Http\Requests\Concerns\HasPasswordRules;
use Modules\Common\Http\Requests\Concerns\HasApiValidation;

class AdminResetPasswordRequest extends FormRequest
{
    use HasApiValidation, HasPasswordRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => $this->passwordRules(),
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => __('messages.validation.password_required'),
            'password.confirmed' => __('messages.validation.password_confirmed'),
            'password.min' => __('messages.validation.password_min'),
            'password.regex' => __('messages.validation.password_regex'),
        ];
    }
}
