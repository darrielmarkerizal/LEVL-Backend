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
        $userId = (int) $this->route('user');

        return [
            'username' => [
                'sometimes',
                'nullable',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-Z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($userId, 'id'),
            ],
            'status' => [
                'sometimes',
                Rule::enum(UserStatus::class)->only([
                    UserStatus::Active,
                    UserStatus::Inactive,
                    UserStatus::Banned,
                ]),
            ],
            'role' => [
                'sometimes',
                'string',
                Rule::in(['Student', 'Instructor', 'Admin', 'Superadmin']),
            ],
            'password' => [
                'sometimes',
                'nullable',
                'string',
                'min:8',
                'max:255',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
                'not_regex:/\s/',
            ],
            'specialization_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:categories,id',
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
            'role.in' => __('validation.in', ['attribute' => 'role']),
            'password.min' => __('validation.min.string', ['attribute' => 'password', 'min' => 8]),
            'password.regex' => __('messages.auth.password_requirements'),
            'password.not_regex' => __('form.user.password_error'),
            'specialization_id.integer' => __('validation.integer', ['attribute' => 'specialization']),
            'specialization_id.exists' => __('validation.exists', ['attribute' => 'specialization']),
        ];
    }
}
