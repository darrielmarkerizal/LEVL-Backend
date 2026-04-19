<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    
    protected array $errors = [];

    
    protected int $statusCode = 500;

    
    protected string $errorCode = 'BUSINESS_ERROR';

    
    public function __construct(string $message, array $errors = [], int $code = 422)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    
    public function getErrors(): array
    {
        return $this->errors;
    }

    
    public function getStatusCode(): int
    {
        return $this->statusCode ?? 500;
    }

    
    public function getErrorCode(): string
    {
        return $this->errorCode ?? 'INTERNAL_SERVER_ERROR';
    }
}
