<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => 'sometimes|string|max:100',
            
            'phone' => 'sometimes|nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'bio' => 'sometimes|nullable|string|max:1000',
            'location' => 'sometimes|nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Nama maksimal 100 karakter.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'bio.max' => 'Bio maksimal 1000 karakter.',
            'location.max' => 'Lokasi maksimal 255 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        
        if ($this->has('email')) {
            $this->request->remove('email');
        }
    }
}
