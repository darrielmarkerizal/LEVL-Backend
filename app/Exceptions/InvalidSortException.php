<?php

namespace App\Exceptions;

use Exception;


class InvalidSortException extends Exception
{
    
    protected array $allowedSorts;

    
    protected string $invalidSort;

    
    public function __construct(string $invalidSort, array $allowedSorts)
    {
        $this->invalidSort = $invalidSort;
        $this->allowedSorts = $allowedSorts;

        $message = sprintf(
            'Invalid sort field: %s. Allowed sorts: %s',
            $invalidSort,
            implode(', ', $allowedSorts)
        );

        parent::__construct($message, 400);
    }

    
    public function getAllowedSorts(): array
    {
        return $this->allowedSorts;
    }

    
    public function getInvalidSort(): string
    {
        return $this->invalidSort;
    }
}
