<?php

declare(strict_types=1);


namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => __('messages.password.current_required'),
            'new_password.required' => __('messages.password.new_required'),
            'new_password.min' => __('messages.password.min_length'),
            'new_password.confirmed' => __('messages.password.confirmation_mismatch'),
            'new_password.regex' => __('messages.password.strength_requirements'),
        ];
    }
}
