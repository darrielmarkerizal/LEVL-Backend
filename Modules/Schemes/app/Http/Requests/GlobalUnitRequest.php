<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Common\Http\Requests\Concerns\HasApiValidation;

class GlobalUnitRequest extends FormRequest
{
    use HasApiValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_slug' => ['required', 'string', 'exists:courses,slug'],
            'code' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('units', 'code'),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                \Illuminate\Validation\Rule::unique('units', 'slug'),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:draft,published'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_slug.required' => __('messages.units.course_slug_required'),
            'course_slug.exists' => __('messages.units.course_slug_not_found'),
            'code.required' => __('messages.units.code_required'),
            'code.unique' => __('messages.units.code_unique'),
            'slug.unique' => __('messages.units.slug_unique'),
            'slug.regex' => __('messages.units.slug_format'),
            'title.required' => __('messages.units.title_required'),
        ];
    }
}
