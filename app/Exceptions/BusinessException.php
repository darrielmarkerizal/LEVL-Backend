<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    /**
     * @var array<string, array<string>>
     */
    protected array $errors = [];

    /**
     * @param  array<string, array<string>>  $errors
     */
    public function __construct(string $message, array $errors = [], int $code = 422)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    /**
     * Get the validation errors.
     *
     * @return array<string, array<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
