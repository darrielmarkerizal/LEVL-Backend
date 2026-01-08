<?php

namespace Modules\Common\Http\Requests\Concerns;

trait HasCommonValidationMessages
{
    protected function commonMessages(): array
    {
        return [
            'required' => __('validation.required'),
            'string' => __('validation.string'),
            'max' => __('validation.max.string'),
            'email' => __('validation.email'),
            'unique' => __('validation.unique'),
            'min' => __('validation.min.string'),
            'confirmed' => __('validation.confirmed'),
            'integer' => __('validation.integer'),
            'numeric' => __('validation.numeric'),
            'array' => __('validation.array'),
            'date' => __('validation.date'),
            'in' => __('validation.in'),
            'exists' => __('validation.exists'),
            'image' => __('validation.image'),
            'file' => __('validation.file'),
            'mimes' => __('validation.mimes'),
        ];
    }
}

