<?php

declare(strict_types=1);

namespace Modules\Notifications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Common\Http\Requests\Concerns\HasApiValidation;

class UploadImageRequest extends FormRequest
{
    use HasApiValidation;

    
    public function authorize(): bool
    {
        return auth('api')->check() && auth('api')->user()->hasRole('Admin');
    }

    
    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,gif,webp',
                'max:5120', 
            ],
            'post_uuid' => [
                'nullable',
                'string',
                'exists:posts,uuid',
            ],
        ];
    }

    
    public function messages(): array
    {
        return [
            'image.required' => __('validation.required', ['attribute' => __('attributes.image')]),
            'image.image' => __('validation.image', ['attribute' => __('attributes.image')]),
            'image.mimes' => __('validation.mimes', [
                'attribute' => __('attributes.image'),
                'values' => 'JPEG, PNG, GIF, WebP',
            ]),
            'image.max' => __('validation.max.file', [
                'attribute' => __('attributes.image'),
                'max' => '5MB',
            ]),
            'post_uuid.exists' => __('validation.exists', ['attribute' => __('attributes.post_uuid')]),
        ];
    }
}
