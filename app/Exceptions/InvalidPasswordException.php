<?php

namespace App\Exceptions;


class InvalidPasswordException extends BusinessException
{
    
    protected int $statusCode = 400;

    
    protected string $errorCode = 'INVALID_PASSWORD';

    
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('validation.current_password'));
    }
}
