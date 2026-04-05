<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:lesson,assignment,quiz'],
            'title' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Tipe konten harus diisi',
            'type.in' => 'Tipe konten harus lesson, assignment, atau quiz',
            'title.required' => 'Judul harus diisi',
            'title.max' => 'Judul maksimal 255 karakter',
        ];
    }
}
