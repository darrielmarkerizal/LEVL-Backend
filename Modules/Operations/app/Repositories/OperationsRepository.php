<?php

namespace Modules\Operations\Repositories;

class OperationsRepository
{
    public function view(string $template): string
    {
        return sprintf('operations::%s', $template);
    }
}
