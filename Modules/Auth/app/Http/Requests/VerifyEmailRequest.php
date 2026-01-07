<?php

declare(strict_types=1);


namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Common\Http\Requests\Concerns\HasApiValidation;
use Modules\Auth\Http\Requests\Concerns\HasAuthRequestRules;

class VerifyEmailRequest extends FormRequest
{
    use HasApiValidation, HasAuthRequestRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->rulesVerifyEmail();
    }

    public function messages(): array
    {
        return $this->messagesVerifyEmail();
    }
}
