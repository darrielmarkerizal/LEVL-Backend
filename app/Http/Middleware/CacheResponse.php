<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;


class CacheResponse
{
    
    public function handle(Request $request, Closure $next, int $ttl = 300): Response
    {
        
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        
        
        
        
        

        $key = $this->getCacheKey($request);

        
        $cachedResponse = Cache::get($key);

        if ($cachedResponse !== null) {
            return response()->json(
                json_decode($cachedResponse, true),
                200,
                ['X-Cache' => 'HIT']
            );
        }

        
        $response = $next($request);

        
        if ($response->isSuccessful() && $response->headers->get('Content-Type') === 'application/json') {
            Cache::put($key, $response->getContent(), $ttl);
            $response->headers->set('X-Cache', 'MISS');
            $response->headers->set('X-Cache-TTL', $ttl);
        }

        return $response;
    }

    
    private function getCacheKey(Request $request): string
    {
        $url = $request->fullUrl();
        $userId = $request->user()?->id ?? 'guest';

        return 'response:'.md5($url.':'.$userId);
    }
}
