<?php

namespace App\Exceptions;


class ResourceNotFoundException extends BusinessException
{
    
    protected int $statusCode = 404;

    
    protected string $errorCode = 'RESOURCE_NOT_FOUND';

    
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('messages.not_found'));
    }
}
