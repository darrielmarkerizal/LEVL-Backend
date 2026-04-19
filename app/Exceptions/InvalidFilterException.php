<?php

namespace App\Exceptions;

use Exception;


class InvalidFilterException extends Exception
{
    
    protected array $allowedFilters;

    
    protected array $invalidFilters;

    
    public function __construct(array $invalidFilters, array $allowedFilters)
    {
        $this->invalidFilters = $invalidFilters;
        $this->allowedFilters = $allowedFilters;

        $message = sprintf(
            'Invalid filter fields: %s. Allowed filters: %s',
            implode(', ', $invalidFilters),
            implode(', ', $allowedFilters)
        );

        parent::__construct($message, 400);
    }

    
    public function getAllowedFilters(): array
    {
        return $this->allowedFilters;
    }

    
    public function getInvalidFilters(): array
    {
        return $this->invalidFilters;
    }
}
