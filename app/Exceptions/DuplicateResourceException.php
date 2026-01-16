<?php

namespace App\Exceptions;

/**
 * Exception thrown when attempting to create a duplicate resource.
 *
 * Returns HTTP 409 status code.
 */
class DuplicateResourceException extends BusinessException
{
    /**
     * HTTP status code for this exception.
     */
    protected int $statusCode = 409;

    /**
     * Application-specific error code.
     */
    protected string $errorCode = 'DUPLICATE_RESOURCE';

    /**
     * Create a new exception instance.
     */
    public function __construct(string|array $data = [])
    {
        if (is_array($data)) {
            parent::__construct(__('messages.conflict'), $data, 409);
        } else {
            parent::__construct($data ?: __('messages.conflict'), [], 409);
        }
    }
}
