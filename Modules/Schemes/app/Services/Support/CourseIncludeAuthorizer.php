<?php

declare(strict_types=1);

namespace Modules\Schemes\Services\Support;

use Modules\Auth\Models\User;
use Modules\Schemes\Models\Course;

class CourseIncludeAuthorizer
{
    private const PUBLIC_INCLUDES = [
        'tags',
        'category',
        'instructor',
        'units',
    ];

    private const ENROLLED_STUDENT_INCLUDES = [
        'lessons',
        'quizzes',
        'assignments',
        'units.lessons',
        'units.lessons.blocks',
    ];

    private const MANAGER_INCLUDES = [
        'enrollments',
        'enrollments.user',
        'admins',
    ];

    public function getPublicIncludes(): array
    {
        return self::PUBLIC_INCLUDES;
    }

    public function filterAllowedIncludes(array $requestedIncludes, ?User $user, Course $course): array
    {
        $allowedIncludes = self::PUBLIC_INCLUDES;

        if ($user) {
            if ($this->isManager($user, $course)) {
                $allowedIncludes = array_merge($allowedIncludes, self::ENROLLED_STUDENT_INCLUDES, self::MANAGER_INCLUDES);
            } elseif ($this->isEnrolledStudent($user, $course)) {
                $allowedIncludes = array_merge($allowedIncludes, self::ENROLLED_STUDENT_INCLUDES);
            }
        }

        return array_values(array_intersect($requestedIncludes, $allowedIncludes));
    }

    private function isManager(User $user, Course $course): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        if ($user->hasRole('Admin')) {
            return $course->admins()->where('user_id', $user->id)->exists();
        }

        if ($user->hasRole('Instructor')) {
            return $course->instructor_id === $user->id;
        }

        return false;
    }

    private function isEnrolledStudent(User $user, Course $course): bool
    {
        return $course->enrollments()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    public function getAllowedIncludesForQueryBuilder(?User $user, Course $course): array
    {
        $allowed = self::PUBLIC_INCLUDES;

        if ($user) {
            if ($this->isManager($user, $course)) {
                $allowed = array_merge($allowed, self::ENROLLED_STUDENT_INCLUDES, self::MANAGER_INCLUDES);
            } elseif ($this->isEnrolledStudent($user, $course)) {
                $allowed = array_merge($allowed, self::ENROLLED_STUDENT_INCLUDES);
            }
        }

        return $allowed;
    }
}
