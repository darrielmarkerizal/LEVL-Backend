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
            /** @var ConnectionInterface $connection */
            $connection = DB::connection($connectionName);

            $rolledBack = 0;

            // Roll back any open transaction levels
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

            // CRITICAL: In long-lived processes (Octane/Swoole), we must fully destroy
            // the PDO connection to clear cached prepared statement handles.
            // If we don't, the next request inherits stale statement references,
            // causing "prepared statement ... does not exist" errors on PostgreSQL
            // and triggering transaction abort (SQLSTATE 25P02).
            $pdo = $connection->getRawPdo();
            if ($pdo instanceof \PDO) {
                try {
                    // Close any remaining transactions in PDO directly
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                } catch (\Throwable) {
                    // ignore, will force disconnect anyway
                }
            }

            // Force disconnect to destroy the PDO object entirely
            DB::disconnect($connectionName);
            DB::purge($connectionName);
        } catch (\Throwable $e) {
            // If anything fails, force a hard reset: disconnect, purge, reconnect
            Log::warning('Database transaction sanitation exception; forcing hard reset.', [
                'connection' => $connectionName,
                'phase' => $phase,
                'error' => $e->getMessage(),
            ]);

            try {
                DB::disconnect($connectionName);
                DB::purge($connectionName);
                DB::reconnect($connectionName);
            } catch (\Throwable $resetError) {
                Log::error('Failed to perform hard reset after transaction sanitation failure.', [
                    'connection' => $connectionName,
                    'phase' => $phase,
                    'error' => $resetError->getMessage(),
                ]);
            }
        }
    }
}
