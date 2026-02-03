<?php

namespace Modules\Forums\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateReplyRequest extends FormRequest
{
     
    public function authorize(): bool
    {
        return true; 
    }

     
    public function rules(): array
    {
        return [
            'content' => 'required|string',
            'parent_id' => 'nullable|integer|exists:replies,id',
        ];
    }

     
    public function messages(): array
    {
        return [
            'content.required' => __('validation.required', ['attribute' => __('validation.attributes.content')]),
            'parent_id.exists' => __('validation.exists', ['attribute' => __('validation.attributes.parent_reply')]),
        ];
    }
}
