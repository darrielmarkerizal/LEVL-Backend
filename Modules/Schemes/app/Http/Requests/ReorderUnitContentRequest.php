<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderUnitContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'array', 'min:1'],
            'content.*.type' => ['required', 'string', 'in:lesson,assignment,quiz'],
            'content.*.id' => ['required', 'integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'content' => __('validation.attributes.content'),
            'content.*.type' => __('validation.attributes.type'),
            'content.*.id' => __('validation.attributes.id'),
        ];
    }
}
