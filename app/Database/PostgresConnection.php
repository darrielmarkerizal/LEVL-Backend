<?php

declare(strict_types=1);

namespace App\Database;

class PostgresConnection extends \Illuminate\Database\PostgresConnection
{
    public function prepareBindings(array $bindings): array
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            }
            
            
            
            
            
            
        }

        return $bindings;
    }
}
