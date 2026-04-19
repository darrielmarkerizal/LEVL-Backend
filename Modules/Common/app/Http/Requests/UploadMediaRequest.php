<?php

declare(strict_types=1);

namespace Modules\Common\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,gif,svg,webp,pdf,mp4',
                'max:51200', 
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
