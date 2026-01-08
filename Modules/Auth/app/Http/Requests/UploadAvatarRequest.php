<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Requests;

use App\Http\Requests\BaseFormRequest;
use App\Support\ValidationRules\ImageRules;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadAvatarRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'avatar' => array_merge(['file'], ImageRules::avatar()),
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => __('validation.required'),
            'avatar.file' => __('messages.auth.avatar_single_file'),
            'avatar.image' => __('validation.image'),
            'avatar.mimes' => __('validation.mimes', ['values' => 'jpeg, png, jpg, gif']),
            'avatar.max' => __('validation.max.file', ['max' => '2048']),
            'avatar.uploaded' => __('messages.auth.avatar_upload_failed'),
        ];
    }

    public function attributes(): array
    {
        return [
            'avatar' => __('validation.attributes.avatar'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => __('messages.validation_failed'),
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
