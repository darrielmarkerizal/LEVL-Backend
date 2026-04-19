<?php

namespace App\Exceptions;


class DuplicateResourceException extends BusinessException
{
    
    protected int $statusCode = 409;

    
    protected string $errorCode = 'DUPLICATE_RESOURCE';

    
    public function __construct(string|array $data = [])
    {
        if (is_array($data)) {
            parent::__construct(__('messages.conflict'), $data, 409);
        } else {
            parent::__construct($data ?: __('messages.conflict'), [], 409);
        }
    }
}
