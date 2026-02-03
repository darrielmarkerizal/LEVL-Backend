<?php

namespace Modules\Forums\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Enrollments\Models\Enrollment;
use Symfony\Component\HttpFoundation\Response;

class CheckForumAccess
{
     
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        
        $schemeId = $request->route('scheme') ?? $request->input('scheme_id');

        if (! $schemeId) {
            return response()->json([
                'success' => false,
                'message' => 'Scheme ID is required.',
            ], 400);
        }

        
        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $schemeId)
            ->exists();

        if (! $isEnrolled) {
            
            if ($user->hasRole('admin')) {
                return $next($request);
            }

            if ($user->hasRole('instructor')) {
                $isInstructor = \Modules\Schemes\Models\Course::where('id', $schemeId)
                    ->where('instructor_id', $user->id)
                    ->exists();

                if ($isInstructor) {
                    return $next($request);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this scheme.',
            ], 403);
        }

        return $next($request);
    }
}
