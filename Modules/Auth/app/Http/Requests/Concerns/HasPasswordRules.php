<?php

declare(strict_types=1);


namespace Modules\Auth\Http\Requests\Concerns;

use Illuminate\Validation\Rules\Password as PasswordRule;

trait HasPasswordRules
{
    /**
     * Strong password rules for resets/changes (includes uncompromised).
     */
    protected function passwordRulesStrong(): array
    {
        return [
            'required',
            'string',
            'confirmed',
            PasswordRule::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),
        ];
    }

    /**
     * Registration password rules (no uncompromised check, faster UX).
     */
    protected function passwordRulesRegistration(): array
    {
        return [
            'required',
            'string',
            'confirmed',
            PasswordRule::min(8)->letters()->mixedCase()->numbers()->symbols(),
        ];
    }

    /**
     * Standard Indonesian messages for password validation.
     */
    protected function passwordMessages(): array
    {
        return [
            'password.required' => __('validation.required', ['attribute' => 'password']),
            'password.string' => __('validation.string', ['attribute' => 'password']),
            'password.confirmed' => __('validation.confirmed', ['attribute' => 'password']),
            'password.min' => __('validation.min.string', ['attribute' => 'password']),
            'password.letters' => __('validation.password.letters'),
            'password.mixed' => __('validation.password.mixed'),
            'password.numbers' => __('validation.password.numbers'),
            'password.symbols' => __('validation.password.symbols'),
            'password.uncompromised' => __('validation.password.uncompromised'),
        ];
    }
}
