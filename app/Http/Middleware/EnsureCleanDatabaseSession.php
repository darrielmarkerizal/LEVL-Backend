<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureCleanDatabaseSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $this->sanitizeConnection('pgsql', 'before_request');

        try {
            return $next($request);
        } finally {
            $this->sanitizeConnection('pgsql', 'after_request');
        }
    }

    private function sanitizeConnection(string $connectionName, string $phase): void
    {
        try {
            
            $connection = DB::connection($connectionName);

            $rolledBack = 0;

            
            while ($connection->transactionLevel() > 0) {
                try {
                    $connection->rollBack();
                    $rolledBack++;
                } catch (\Throwable) {
                    break;
                }
            }

            if ($rolledBack > 0) {
                Log::warning('Forced rollback to clean stale transaction state.', [
                    'connection' => $connectionName,
                    'phase' => $phase,
                    'rolled_back_levels' => $rolledBack,
                    'path' => request()->path(),
                ]);
            }
        } catch (\Throwable $e) {
            
            DB::disconnect($connectionName);
            DB::purge($connectionName);

            try {
                DB::reconnect($connectionName);
            } catch (\Throwable $reconnectError) {
                Log::error('Failed to reconnect database after transaction sanitation failure.', [
                    'connection' => $connectionName,
                    'phase' => $phase,
                    'error' => $reconnectError->getMessage(),
                ]);
            }

            Log::warning('Database transaction sanitation failed; connection was reset.', [
                'connection' => $connectionName,
                'phase' => $phase,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
