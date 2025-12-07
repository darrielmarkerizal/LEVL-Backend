<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MasterDataStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => 'required|string|max:100',
            'label' => 'required|string|max:255',
            'metadata' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'Value wajib diisi',
            'value.max' => 'Value maksimal 100 karakter',
            'label.required' => 'Label wajib diisi',
            'label.max' => 'Label maksimal 255 karakter',
        ];
    }
}
