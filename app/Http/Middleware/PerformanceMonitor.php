<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PerformanceMonitor
{
    public function handle(Request $request, Closure $next)
    {
        if (! app()->bound(\Laravel\Octane\Octane::class)) {
            
            return $next($request);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = ($endTime - $startTime) * 1000; 
        $memoryUsed = $endMemory - $startMemory;

        
        if ($executionTime > 100) {
            Log::warning('Slow request detected in Octane', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time_ms' => round($executionTime, 2),
                'memory_used_bytes' => $memoryUsed,
                'timestamp' => now()->toISOString(),
            ]);
        }

        return $response;
    }
}
