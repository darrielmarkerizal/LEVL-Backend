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
                'mimes:jpeg,jpg,png,gif,svg,webp,bmp,mp4,webm,ogg,mov,avi,mkv,mpeg,pdf,txt,csv,doc,docx,xls,xlsx,ppt,pptx,rtf,zip,rar,7z,tar,gz,json,xml',
                'max:51200',
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
