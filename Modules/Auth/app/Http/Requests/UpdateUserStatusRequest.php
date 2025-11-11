<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Auth\Http\Requests\Concerns\HasApiValidation;
use Modules\Auth\Http\Requests\Concerns\HasCommonValidationMessages;

class UpdateUserStatusRequest extends FormRequest
{
    use HasApiValidation, HasCommonValidationMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:active,inactive,banned'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status hanya boleh salah satu dari: active, inactive, atau banned.',
        ];
    }
}
