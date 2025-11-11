<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Auth\Http\Requests\Concerns\HasApiValidation;

class VerifyEmailByTokenRequest extends FormRequest
{
    use HasApiValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'size:16'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Token verifikasi wajib diisi.',
            'token.string' => 'Token verifikasi harus berupa teks.',
            'token.size' => 'Token verifikasi harus 16 karakter.',
        ];
    }
}

