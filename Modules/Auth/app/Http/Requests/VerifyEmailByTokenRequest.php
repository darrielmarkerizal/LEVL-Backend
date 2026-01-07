<?php

declare(strict_types=1);


namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Common\Http\Requests\Concerns\HasApiValidation;

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
            'uuid' => ['required', 'string', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Token verifikasi wajib diisi.',
            'token.string' => 'Token verifikasi harus berupa teks.',
            'token.size' => 'Token verifikasi harus 16 karakter.',
            'uuid.required' => 'UUID verifikasi wajib diisi.',
            'uuid.string' => 'UUID verifikasi harus berupa teks.',
            'uuid.uuid' => 'UUID verifikasi tidak valid.',
        ];
    }
}

