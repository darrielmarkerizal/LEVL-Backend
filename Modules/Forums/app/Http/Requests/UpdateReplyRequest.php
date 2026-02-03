<?php

namespace Modules\Forums\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReplyRequest extends FormRequest
{
     
    public function authorize(): bool
    {
        return true; 
    }

     
    public function rules(): array
    {
        return [
            'content' => 'required|string',
        ];
    }

     
    public function messages(): array
    {
        return [
            'content.required' => __('validation.required', ['attribute' => __('validation.attributes.content')]),
        ];
    }
}
