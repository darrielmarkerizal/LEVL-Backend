<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublishCourseRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true; 
    }

    
    public function rules(): array
    {
        return [
            'enrollment_type' => ['required', 'in:auto_accept,key_based,approval'],
            'enrollment_key' => ['nullable', 'string', 'max:100'],
        ];
    }

    
    public function attributes(): array
    {
        return [
            'enrollment_type' => __('validation.attributes.enrollment_type'),
            'enrollment_key' => __('validation.attributes.enrollment_key'),
        ];
    }
}
