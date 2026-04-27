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
            // Booleans are intentionally NOT converted to integers here.
            // Laravel's default Connection::prepareBindings() does (int) $value
            // for booleans, which causes PostgreSQL to reject them with
            // "operator does not exist: boolean = integer".
            // PDO::PARAM_BOOL (used when a PHP bool is passed natively) is
            // correctly handled by the PostgreSQL PDO driver.
        }

        return $bindings;
    }
}
