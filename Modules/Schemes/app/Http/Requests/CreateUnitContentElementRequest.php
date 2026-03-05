<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUnitContentElementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:lesson,quiz,assignment'],
            'title' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => __('validation.attributes.type'),
            'title' => __('validation.attributes.title'),
        ];
    }
}
