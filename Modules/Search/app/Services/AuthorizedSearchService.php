<?php

namespace Modules\Search\Services;

use Illuminate\Support\Collection;
use Modules\Assignments\Models\Assignment;
use Modules\Auth\Models\User;
use Modules\Forums\Models\Thread;
use Modules\Quizzes\Models\Quiz;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;

/**
 * Authorized Search Service
 *
 * Implements role-based access control for search functionality
 */
class AuthorizedSearchService
{
    /**
     * Perform authorized search based on user role
     */
    public function search(string $query, string $type, ?User $user = null): array
    {
        // If no user (public search), only allow courses and units
        if (! $user) {
            return $this->publicSearch($query, $type);
        }

        return match ($type) {
            'courses' => ['courses' => $this->searchCourses($query, $user)],
            'units' => ['units' => $this->searchUnits($query, $user)],
            'lessons' => ['lessons' => $this->searchLessons($query, $user)],
            'assignments' => ['assignments' => $this->searchAssignments($query, $user)],
            'quizzes' => ['quizzes' => $this->searchQuizzes($query, $user)],
            'users' => ['users' => $this->searchUsers($query, $user)],
            'forums' => ['forums' => $this->searchForums($query, $user)],
            'content' => $this->searchAllContent($query, $user),
            'all' => $this->globalSearch($query, $user),
            default => throw new \InvalidArgumentException("Invalid search type: {$type}"),
        };
    }

    /**
     * Public search (no authentication)
     * Only courses and units are accessible
     */
    protected function publicSearch(string $query, string $type): array
    {
        if (! in_array($type, ['courses', 'units', 'all'])) {
            throw new \UnauthorizedException('Authentication required for this search type');
        }

        return match ($type) {
            'courses' => ['courses' => $this->searchCoursesPublic($query)],
            'units' => ['units' => $this->searchUnitsPublic($query)],
            'all' => [
                'courses' => $this->searchCoursesPublic($query),
                'units' => $this->searchUnitsPublic($query),
            ],
        };
    }

    /**
     * Search courses (PUBLIC - all roles can access)
     */
    protected function searchCourses(string $query, User $user): Collection
    {
        return Course::search($query)
            ->where('status', 'published')
            ->get();
    }

    protected function searchCoursesPublic(string $query): Collection
    {
        return Course::search($query)
            ->where('status', 'published')
            ->get();
    }

    /**
     * Search units (PUBLIC - all roles can access)
     */
    protected function searchUnits(string $query, User $user): Collection
    {
        return Unit::search($query)->get();
    }

    protected function searchUnitsPublic(string $query): Collection
    {
        return Unit::search($query)->get();
    }

    /**
     * Search lessons (RESTRICTED - role-based access)
     */
    protected function searchLessons(string $query, User $user): Collection
    {
        $baseQuery = Lesson::search($query);

        // Admin/SuperAdmin - full access
        if ($user->hasAnyRole(['Admin', 'SuperAdmin'])) {
            return $baseQuery->get();
        }

        // Instructor - only managed courses
        if ($user->hasRole('Instructor')) {
            return $this->filterLessonsForInstructor($baseQuery, $user);
        }

        // Student - only enrolled courses
        if ($user->hasRole('Student')) {
            return $this->filterLessonsForStudent($baseQuery, $user);
        }

        // Default - no access
        return collect([]);
    }

    /**
     * Filter lessons for student (enrolled courses only)
     */
    protected function filterLessonsForStudent($query, User $user): Collection
    {
        $enrolledCourseIds = $this->getEnrolledCourseIds($user->id);

        if ($enrolledCourseIds->isEmpty()) {
            return collect([]);
        }

        return $query->whereIn('course_id', $enrolledCourseIds->toArray())->get();
    }

    /**
     * Filter lessons for instructor (managed courses only)
     */
    protected function filterLessonsForInstructor($query, User $user): Collection
    {
        $managedCourseIds = $this->getManagedCourseIds($user->id);

        if ($managedCourseIds->isEmpty()) {
            return collect([]);
        }

        return $query->whereIn('course_id', $managedCourseIds->toArray())->get();
    }

    /**
     * Search assignments (RESTRICTED - role-based access)
     */
    protected function searchAssignments(string $query, User $user): Collection
    {
        $baseQuery = Assignment::search($query);

        // Admin/SuperAdmin - full access
        if ($user->hasAnyRole(['Admin', 'SuperAdmin'])) {
            return $baseQuery->get();
        }

        // Instructor - only managed courses
        if ($user->hasRole('Instructor')) {
            $managedCourseIds = $this->getManagedCourseIds($user->id);
            if ($managedCourseIds->isEmpty()) {
                return collect([]);
            }

            return $baseQuery->whereIn('course_id', $managedCourseIds->toArray())->get();
        }

        // Student - only enrolled courses
        if ($user->hasRole('Student')) {
            $enrolledCourseIds = $this->getEnrolledCourseIds($user->id);
            if ($enrolledCourseIds->isEmpty()) {
                return collect([]);
            }

            return $baseQuery->whereIn('course_id', $enrolledCourseIds->toArray())->get();
        }

        return collect([]);
    }

    /**
     * Search quizzes (RESTRICTED - role-based access)
     */
    protected function searchQuizzes(string $query, User $user): Collection
    {
        $baseQuery = Quiz::search($query);

        // Admin/SuperAdmin - full access
        if ($user->hasAnyRole(['Admin', 'SuperAdmin'])) {
            return $baseQuery->get();
        }

        // Instructor - only managed courses
        if ($user->hasRole('Instructor')) {
            $managedCourseIds = $this->getManagedCourseIds($user->id);
            if ($managedCourseIds->isEmpty()) {
                return collect([]);
            }

            return $baseQuery->whereIn('course_id', $managedCourseIds->toArray())->get();
        }

        // Student - only enrolled courses
        if ($user->hasRole('Student')) {
            $enrolledCourseIds = $this->getEnrolledCourseIds($user->id);
            if ($enrolledCourseIds->isEmpty()) {
                return collect([]);
            }

            return $baseQuery->whereIn('course_id', $enrolledCourseIds->toArray())->get();
        }

        return collect([]);
    }

    /**
     * Search users (RESTRICTED - role-based access)
     */
    protected function searchUsers(string $query, User $user): Collection
    {
        $baseQuery = User::search($query);

        // Admin/SuperAdmin - full access to all users
        if ($user->hasAnyRole(['Admin', 'SuperAdmin'])) {
            return $baseQuery->get();
        }

        // Instructor - can search all students
        if ($user->hasRole('Instructor')) {
            return $baseQuery->whereHas('roles', function ($q) {
                $q->where('name', 'Student');
            })->get();
        }

        // Student - can only search other students
        if ($user->hasRole('Student')) {
            return $baseQuery->whereHas('roles', function ($q) {
                $q->where('name', 'Student');
            })->get();
        }

        return collect([]);
    }

    /**
     * Search forums (RESTRICTED - role-based access)
     */
    protected function searchForums(string $query, User $user): Collection
    {
        $baseQuery = Thread::search($query);

        // Admin/SuperAdmin - full access
        if ($user->hasAnyRole(['Admin', 'SuperAdmin'])) {
            return $baseQuery->get();
        }

        // Instructor - only managed courses
        if ($user->hasRole('Instructor')) {
            $managedCourseIds = $this->getManagedCourseIds($user->id);
            if ($managedCourseIds->isEmpty()) {
                return collect([]);
            }

            return $baseQuery->whereIn('course_id', $managedCourseIds->toArray())->get();
        }

        // Student - only enrolled courses
        if ($user->hasRole('Student')) {
            $enrolledCourseIds = $this->getEnrolledCourseIds($user->id);
            if ($enrolledCourseIds->isEmpty()) {
                return collect([]);
            }

            return $baseQuery->whereIn('course_id', $enrolledCourseIds->toArray())->get();
        }

        return collect([]);
    }

    /**
     * Search all content (lessons, assignments, quizzes)
     */
    protected function searchAllContent(string $query, User $user): array
    {
        return [
            'lessons' => $this->searchLessons($query, $user),
            'assignments' => $this->searchAssignments($query, $user),
            'quizzes' => $this->searchQuizzes($query, $user),
        ];
    }

    /**
     * Global search across all resources
     */
    protected function globalSearch(string $query, User $user): array
    {
        return [
            'courses' => $this->searchCourses($query, $user),
            'units' => $this->searchUnits($query, $user),
            'lessons' => $this->searchLessons($query, $user),
            'assignments' => $this->searchAssignments($query, $user),
            'quizzes' => $this->searchQuizzes($query, $user),
            'users' => $this->searchUsers($query, $user),
            'forums' => $this->searchForums($query, $user),
        ];
    }

    /**
     * Get enrolled course IDs for a student
     * Only courses with status 'active' or 'completed'
     */
    protected function getEnrolledCourseIds(int $userId): Collection
    {
        return \DB::table('enrollments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'completed'])
            ->pluck('course_id');
    }

    /**
     * Get managed course IDs for an instructor
     */
    protected function getManagedCourseIds(int $userId): Collection
    {
        return Course::where('instructor_id', $userId)
            ->pluck('id');
    }

    /**
     * Check if user has valid enrollment for a course
     */
    public function hasValidEnrollment(int $userId, int $courseId): bool
    {
        return \DB::table('enrollments')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->whereIn('status', ['active', 'completed'])
            ->exists();
    }

    /**
     * Check if user manages a course
     */
    public function managesCourse(int $userId, int $courseId): bool
    {
        return Course::where('id', $courseId)
            ->where('instructor_id', $userId)
            ->exists();
    }
}
