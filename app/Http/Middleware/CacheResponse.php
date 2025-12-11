<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Response Caching Middleware
 *
 * Caches GET request responses to improve performance for frequently accessed endpoints.
 * Only caches successful (200) responses for GET requests.
 *
 * Usage in routes:
 * ```php
 * Route::get('/products', [ProductController::class, 'index'])
 *     ->middleware('cache.response:300'); // Cache for 5 minutes
 * ```
 */
class CacheResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  int  $ttl  Cache time-to-live in seconds (default: 300 = 5 minutes)
     */
    public function handle(Request $request, Closure $next, int $ttl = 300): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Skip caching for authenticated admin/instructor requests if needed
        // Uncomment if you want to disable caching for certain roles
        // if ($request->user() && $request->user()->hasAnyRole(['Admin', 'Instructor'])) {
        //     return $next($request);
        // }

        $key = $this->getCacheKey($request);

        // Try to get cached response
        $cachedResponse = Cache::get($key);

        if ($cachedResponse !== null) {
            return response()->json(
                json_decode($cachedResponse, true),
                200,
                ['X-Cache' => 'HIT']
            );
        }

        // Get fresh response
        $response = $next($request);

        // Only cache successful JSON responses
        if ($response->isSuccessful() && $response->headers->get('Content-Type') === 'application/json') {
            Cache::put($key, $response->getContent(), $ttl);
            $response->headers->set('X-Cache', 'MISS');
            $response->headers->set('X-Cache-TTL', $ttl);
        }

        return $response;
    }

    /**
     * Generate cache key from request.
     *
     * @param  Request  $request
     * @return string
     */
    private function getCacheKey(Request $request): string
    {
        $url = $request->fullUrl();
        $userId = $request->user()?->id ?? 'guest';
        
        return 'response:'.md5($url.':'.$userId);
    }
}
