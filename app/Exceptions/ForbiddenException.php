<?php

namespace App\Exceptions;


class ForbiddenException extends BusinessException
{
    
    protected int $statusCode = 403;

    
    protected string $errorCode = 'FORBIDDEN';

    
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('messages.forbidden'));
    }
}
