<?php

declare(strict_types=1);

namespace Modules\Common\Http\Requests\Concerns;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait HasApiValidation
{
    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        
        // Get first error message as main message
        $firstError = '';
        if (! empty($errors)) {
            $firstErrorArray = reset($errors);
            if (is_array($firstErrorArray) && ! empty($firstErrorArray)) {
                $firstError = $firstErrorArray[0];
            }
        }
        
        $response = response()->json([
            'success' => false,
            'message' => $firstError ?: __('messages.validation.failed'),
            'errors' => $errors,
            'data' => null,
            'meta' => null,
        ], 422);
        
        throw new HttpResponseException($response);
    }
}
