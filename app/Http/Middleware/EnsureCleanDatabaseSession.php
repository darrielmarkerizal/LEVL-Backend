<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureCleanDatabaseSession
{
    private const MUTATION_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $this->sanitizeOnEntry($request);

        try {
            return $next($request);
        } finally {
            $this->sanitizeOnExit($request);
        }
    }

    private function sanitizeOnEntry(Request $request): void
    {
        $connectionName = DB::getDefaultConnection();
        $connection = DB::connection($connectionName);
        $level = $connection->transactionLevel();

        if ($level > 0) {
            Log::warning('Stale transaction detected at request start — forcing rollback.', [
                'path' => $request->path(),
                'method' => $request->method(),
                'transaction_level' => $level,
            ]);

            $rolled = 0;
            while ($connection->transactionLevel() > 0) {
                try {
                    $connection->rollBack();
                    $rolled++;
                } catch (\Throwable) {
                    break;
                }
            }

            if ($rolled === 0) {
                $this->forceReconnect($connectionName, $request, 'entry_rollback_failed');
            }
        }

        if (in_array($request->method(), self::MUTATION_METHODS, true)) {
            $this->forceReconnect($connectionName, $request, 'pre_mutation');
        }
    }

    private function sanitizeOnExit(Request $request): void
    {
        $connectionName = DB::getDefaultConnection();
        $connection = DB::connection($connectionName);
        $level = $connection->transactionLevel();

        if ($level > 0) {
            Log::warning('Open transaction detected after request — forcing rollback.', [
                'path' => $request->path(),
                'method' => $request->method(),
                'transaction_level' => $level,
            ]);

            while ($connection->transactionLevel() > 0) {
                try {
                    $connection->rollBack();
                } catch (\Throwable) {
                    break;
                }
            }
        }
    }

    private function forceReconnect(string $connectionName, Request $request, string $reason): void
    {
        try {
            DB::purge($connectionName);
            DB::reconnect($connectionName);
        } catch (\Throwable $e) {
            Log::error('Failed to force-reconnect database connection.', [
                'connection' => $connectionName,
                'reason' => $reason,
                'path' => $request->path(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
