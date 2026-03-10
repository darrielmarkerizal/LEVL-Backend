<?php

declare(strict_types=1);

namespace Modules\Trash\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkForceDeleteTrashBinsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:trash_bins,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
