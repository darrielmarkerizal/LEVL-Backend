<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Common\Http\Requests\Concerns\HasApiValidation;

class BulkExportRequest extends FormRequest
{
    use HasApiValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'email' => ['nullable', 'email'],
            'filter' => ['nullable', 'array'],
            'filter.status' => ['nullable', 'string'],
            'filter.role' => ['nullable', 'string'],
            'search' => ['nullable', 'string'],
        ];
    }
}
