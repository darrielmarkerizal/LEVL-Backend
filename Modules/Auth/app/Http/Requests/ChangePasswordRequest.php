<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Auth\Http\Requests\Concerns\HasPasswordRules;
use Modules\Common\Http\Requests\Concerns\HasApiValidation;

class ChangePasswordRequest extends FormRequest
{
    use HasApiValidation, HasPasswordRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'new_password' => $this->passwordRulesWithoutConfirmation(),
        ];
    }

    public function messages(): array
    {
        return array_merge($this->newPasswordMessages(), [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.string' => 'Kata sandi saat ini harus berupa teks.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
        ]);
    }
}
