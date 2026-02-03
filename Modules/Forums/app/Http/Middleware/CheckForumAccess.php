<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Models\Course;
use Symfony\Component\HttpFoundation\Response;

class CheckForumAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $courseId = $request->route('scheme') ?? $request->input('course_id');

        if (!$courseId) {
            return response()->json([
                'success' => false,
                'message' => 'Course ID is required.',
            ], 400);
        }

        if ($user->hasRole('admin')) {
            return $next($request);
        }

        if ($user->hasRole('instructor')) {
            $isInstructor = Course::where('id', (int) $courseId)
                ->where('instructor_id', $user->id)
                ->exists();

            if ($isInstructor) {
                return $next($request);
            }
        }

        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', (int) $courseId)
            ->exists();

        if (!$isEnrolled) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this forum.',
            ], 403);
        }

        return $next($request);
    }
}
