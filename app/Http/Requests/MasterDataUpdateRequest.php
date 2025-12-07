<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MasterDataUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => 'sometimes|string|max:100',
            'label' => 'sometimes|string|max:255',
            'metadata' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'value.max' => 'Value maksimal 100 karakter',
            'label.max' => 'Label maksimal 255 karakter',
        ];
    }
}
