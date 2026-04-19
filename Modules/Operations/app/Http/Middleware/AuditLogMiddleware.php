<?php

namespace Modules\Operations\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Common\Models\AuditLog;

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        
        if ($response->isSuccessful() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->logAudit($request, $response);
        }

        return $response;
    }

    private function logAudit(Request $request, $response): void
    {
        try {
            $route = $request->route();
            if (! $route) {
                return;
            }

            $routeName = $route->getName();
            $auditableRoutes = [
                'enrollments.approve' => 'enrollment_approved',
                'enrollments.decline' => 'enrollment_declined',
                'enrollments.remove' => 'enrollment_removed',
                'courses.enrollments.cancel' => 'enrollment_cancelled',
                'courses.enrollment-key.generate' => 'enrollment_key_generated',
                'courses.enrollment-key' => 'enrollment_key_updated',
            ];

            if (isset($auditableRoutes[$routeName])) {
                
                $user = auth('api')->user();

                AuditLog::logAction(
                    action: $auditableRoutes[$routeName],
                    subject: $this->extractModelFromRoute($route),
                    actor: $user,
                    context: [
                        'route_name' => $routeName,
                        'method' => $request->method(),
                        'url' => $request->fullUrl(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]
                );
            }
        } catch (\Exception $e) {
            
            Log::error('Audit logging failed: '.$e->getMessage());
        }
    }

    private function extractModelFromRoute($route)
    {
        $parameters = $route->parameters();

        if (isset($parameters['enrollment'])) {
            return $parameters['enrollment'];
        }
        if (isset($parameters['course'])) {
            return $parameters['course'];
        }

        return null;
    }
}
