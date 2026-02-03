<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|min:3|max:255',
            'content' => 'sometimes|string|min:1|max:5000|required_unless:title',
        ];
    }

    public function messages(): array
    {
        return [
            'title.min' => __('validation.min.string', ['attribute' => __('validation.attributes.title'), 'min' => 3]),
            'title.max' => __('validation.max.string', ['attribute' => __('validation.attributes.title'), 'max' => 255]),
            'content.min' => __('validation.min.string', ['attribute' => __('validation.attributes.content'), 'min' => 1]),
            'content.max' => __('validation.max.string', ['attribute' => __('validation.attributes.content'), 'max' => 5000]),
            'content.required_unless' => __('validation.required_unless', ['attribute' => __('validation.attributes.content'), 'other' => __('validation.attributes.title')]),
        ];
    }
}
