<?php

namespace App\Exceptions;


class UnauthorizedException extends BusinessException
{
    
    protected int $statusCode = 401;

    
    protected string $errorCode = 'UNAUTHORIZED';

    
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('messages.unauthenticated'));
    }
}
