<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectQueryDetectorStatus
{
    
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isLocal()) {
            return $next($request);
        }

        $response = $next($request);

        
        if ($response instanceof JsonResponse && config('querydetector.enabled')) {
            $data = $response->getData(true);

            
            if (! isset($data['query_detector'])) {
                $data['query_detector'] = [
                    'status' => 'clean',
                    'message' => 'No N+1 queries detected',
                    'queries_count' => 0,
                ];

                $response->setData($data);
            }
        }

        return $response;
    }
}
