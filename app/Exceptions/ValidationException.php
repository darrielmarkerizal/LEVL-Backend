<?php

namespace App\Exceptions;


class ValidationException extends BusinessException
{
    
    protected int $statusCode = 422;

    
    protected string $errorCode = 'VALIDATION_ERROR';

    
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('messages.validation_failed'));
    }
}
