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


class AuthorizedSearchService
{
    
    public function search(string $query, string $type, ?User $user = null): array
    {
        
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

    
    protected function searchUnits(string $query, User $user): Collection
    {
        return Unit::search($query)->get();
    }

    protected function searchUnitsPublic(string $query): Collection
    {
        return Unit::search($query)->get();
    }

    
    protected function searchLessons(string $query, User $user): Collection
    {
        $baseQuery = Lesson::search($query);

        
        if ($user->hasAnyRole(['Admin', 'SuperAdmin'])) {
            return $baseQuery->get();
        }

        
        if ($user->hasRole('Instructor')) {
            return $this->filterLessonsForInstructor($baseQuery, $user);
        }

        
        if ($user->hasRole('Student')) {
            return $this->filterLessonsForStudent($baseQuery, $user);
        }

        
        return collect([]);
    }

    
    protected function filterLessonsForStudent($query, User $user): Collection
    {
        $enrolledCourseIds = $this->getEnrolledCourseIds($user->id);

        if ($enrolledCourseIds->isEmpty()) {
            return collect([]);
        }

        return $query->whereIn('course_id', $enrolledCourseIds->toArray())->get();
    }

    
    protected function filterLessonsForInstructor($query, User $user): Collection
    {
        $managedCourseIds = $this->getManagedCourseIds($user->id);

        if ($managedCourseIds->isEmpty()) {
            return collect([]);
        }

        return $query->whereIn('course_id', $managedCourseIds->toArray())->get();
    }

    
    protected function searchAssignments(string $query, User $user): Collection
    {
        $baseQuery = Assignment::search($query);

        
        if ($user->hasAnyRole(['Admin', 'SuperAdmin'])) {
            return $baseQuery->get();
        }

        
        if ($user->hasRole('Instructor')) {
            $managedCourseIds = $this->getManagedCourseIds($user->id);
            if ($managedCourseIds->isEmpty()) {
                return collect([]);
            }

            return $baseQuery->whereIn('course_id', $managedCourseIds->toArray())->get();
        }

        
        if ($user->hasRole('Student')) {
            $enrolledCourseIds = $this->getEnrolledCourseIds($user->id);
            if ($enrolledCourseIds->isEmpty()) {
                return collect([]);
            }

            return $baseQuery->whereIn('course_id', $enrolledCourseIds->toArray())->get();
        }

        return collect([]);
    }

    
    protected function searchQuizzes(string $query, User $user): Collection
    {
        $baseQuery = Quiz::search($query);

        
        if ($user->hasAnyRole(['Admin', 'SuperAdmin'])) {
            return $baseQuery->get();
        }

        
        if ($user->hasRole('Instructor')) {
            $managedCourseIds = $this->getManagedCourseIds($user->id);
            if ($managedCourseIds->isEmpty()) {
                return collect([]);
            }

            return $baseQuery->whereIn('course_id', $managedCourseIds->toArray())->get();
        }

        
        if ($user->hasRole('Student')) {
            $enrolledCourseIds = $this->getEnrolledCourseIds($user->id);
            if ($enrolledCourseIds->isEmpty()) {
                return collect([]);
            }

            return $baseQuery->whereIn('course_id', $enrolledCourseIds->toArray())->get();
        }

        return collect([]);
    }

    
    protected function searchUsers(string $query, User $user): Collection
    {
        $baseQuery = User::search($query);

        
        if ($user->hasAnyRole(['Admin', 'SuperAdmin'])) {
            return $baseQuery->get();
        }

        
        if ($user->hasRole('Instructor')) {
            return $baseQuery->whereHas('roles', function ($q) {
                $q->where('name', 'Student');
            })->get();
        }

        
        if ($user->hasRole('Student')) {
            return $baseQuery->whereHas('roles', function ($q) {
                $q->where('name', 'Student');
            })->get();
        }

        return collect([]);
    }

    
    protected function searchForums(string $query, User $user): Collection
    {
        $baseQuery = Thread::search($query);

        
        if ($user->hasAnyRole(['Admin', 'SuperAdmin'])) {
            return $baseQuery->get();
        }

        
        if ($user->hasRole('Instructor')) {
            $managedCourseIds = $this->getManagedCourseIds($user->id);
            if ($managedCourseIds->isEmpty()) {
                return collect([]);
            }

            return $baseQuery->whereIn('course_id', $managedCourseIds->toArray())->get();
        }

        
        if ($user->hasRole('Student')) {
            $enrolledCourseIds = $this->getEnrolledCourseIds($user->id);
            if ($enrolledCourseIds->isEmpty()) {
                return collect([]);
            }

            return $baseQuery->whereIn('course_id', $enrolledCourseIds->toArray())->get();
        }

        return collect([]);
    }

    
    protected function searchAllContent(string $query, User $user): array
    {
        return [
            'lessons' => $this->searchLessons($query, $user),
            'assignments' => $this->searchAssignments($query, $user),
            'quizzes' => $this->searchQuizzes($query, $user),
        ];
    }

    
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

    
    protected function getEnrolledCourseIds(int $userId): Collection
    {
        return \DB::table('enrollments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'completed'])
            ->pluck('course_id');
    }

    
    protected function getManagedCourseIds(int $userId): Collection
    {
        return Course::where('instructor_id', $userId)
            ->pluck('id');
    }

    
    public function hasValidEnrollment(int $userId, int $courseId): bool
    {
        return \DB::table('enrollments')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->whereIn('status', ['active', 'completed'])
            ->exists();
    }

    
    public function managesCourse(int $userId, int $courseId): bool
    {
        return Course::where('id', $courseId)
            ->where('instructor_id', $userId)
            ->exists();
    }
}
