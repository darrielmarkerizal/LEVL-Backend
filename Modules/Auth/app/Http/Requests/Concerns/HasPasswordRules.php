<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Requests\Concerns;

use Illuminate\Validation\Rules\Password as PasswordRule;

trait HasPasswordRules
{
    
    protected function passwordRules(): array
    {
        return [
            'required',
            'string',
            'confirmed',
            PasswordRule::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised(),
        ];
    }

    
    protected function passwordRulesWithoutConfirmation(): array
    {
        return [
            'required',
            'string',
            PasswordRule::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised(),
        ];
    }

    
    protected function passwordRulesStrong(): array
    {
        return $this->passwordRules();
    }

    
    protected function passwordRulesRegistration(): array
    {
        return $this->passwordRules();
    }

    
    protected function passwordMessages(): array
    {
        return [
            'password.required' => 'Kata sandi wajib diisi.',
            'password.string' => 'Kata sandi harus berupa teks.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal harus 8 karakter.',
        ];
    }

    
    protected function newPasswordMessages(): array
    {
        return [
            'new_password.required' => 'Kata sandi baru wajib diisi.',
            'new_password.string' => 'Kata sandi baru harus berupa teks.',
            'new_password.min' => 'Kata sandi baru minimal harus 8 karakter.',
        ];
    }
}
