<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Auth\Enums\UserStatus;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'username' => [
                'sometimes',
                'nullable',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-Z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'status' => [
                'sometimes',
                Rule::enum(UserStatus::class)->only([
                    UserStatus::Active,
                    UserStatus::Inactive,
                    UserStatus::Banned,
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'username.min' => __('validation.min.string', ['attribute' => 'username', 'min' => 3]),
            'username.max' => __('validation.max.string', ['attribute' => 'username', 'max' => 255]),
            'username.regex' => __('validation.regex', ['attribute' => 'username']),
            'username.unique' => __('validation.unique', ['attribute' => 'username']),
            'status.enum' => __('validation.enum', ['attribute' => 'status']),
        ];
    }
}
