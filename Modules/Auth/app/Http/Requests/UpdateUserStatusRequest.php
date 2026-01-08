<?php

declare(strict_types=1);


namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Auth\Enums\UserStatus;

class UpdateUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(UserStatus::class)->only([UserStatus::Active, UserStatus::Inactive, UserStatus::Banned])],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => __('validation.required', ['attribute' => 'status']),
            'status.enum' => __('validation.enum', ['attribute' => 'status']),
        ];
    }
}
