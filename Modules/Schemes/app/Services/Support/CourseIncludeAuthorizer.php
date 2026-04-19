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
        'units',
        'outcomes',
    ];

    private const ENROLLED_STUDENT_INCLUDES = [
        'lessons',
        'quizzes',
        'assignments',
        'units.lessons',
    ];

    private const MANAGER_INCLUDES = [
        'enrollments',
        'enrollments.user',
        'instructors',
        'instructors.specialization',
        'instructor',
        'instructor.specialization',
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
            return true; 
        }

        if ($user->hasRole('Instructor')) {
            return $course->instructors()->where('user_id', $user->id)->exists();
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
        $allowed = [
            'tags',
            'category',
            'units',
            'outcomes',
        ];

        if ($user) {
            if ($this->isManager($user, $course)) {
                
                $allowed = array_merge($allowed, [
                    'lessons',
                    'quizzes',
                    'assignments',
                    'units.lessons',
                ]);

                
                $allowed[] = 'enrollments';
                $allowed[] = 'enrollments.user';
                $allowed[] = 'instructors';
                $allowed[] = 'instructor';
                $allowed[] = \Spatie\QueryBuilder\AllowedInclude::relationship('instructorList', 'instructors');
                $allowed[] = \Spatie\QueryBuilder\AllowedInclude::relationship('instructors.specialization');
                $allowed[] = \Spatie\QueryBuilder\AllowedInclude::relationship('instructor.specialization');
                $allowed[] = \Spatie\QueryBuilder\AllowedInclude::count('instructorCount', 'instructors');
                $allowed[] = \Spatie\QueryBuilder\AllowedInclude::count('enrollmentsCount', 'enrollments');
            } elseif ($this->isEnrolledStudent($user, $course)) {
                $allowed = array_merge($allowed, [
                    'lessons',
                    'quizzes',
                    'assignments',
                    'units.lessons',
                ]);
            }
        }

        return $allowed;
    }

    public function getAllowedIncludesForIndex(?User $user): array
    {
        $allowed = [
            'tags',
            'category',
            'units',
            'outcomes',
        ];

        if ($user && $user->hasAnyRole(['Superadmin', 'Admin', 'Instructor'])) {
            $allowed[] = 'instructor';
            $allowed[] = 'instructors';
            $allowed[] = \Spatie\QueryBuilder\AllowedInclude::relationship('instructorList', 'instructors');
            $allowed[] = \Spatie\QueryBuilder\AllowedInclude::relationship('instructors.specialization');
            $allowed[] = \Spatie\QueryBuilder\AllowedInclude::relationship('instructor.specialization');
            $allowed[] = \Spatie\QueryBuilder\AllowedInclude::count('instructorCount', 'instructors');
            $allowed[] = 'enrollments';
            $allowed[] = \Spatie\QueryBuilder\AllowedInclude::count('enrollmentsCount', 'enrollments');
        }

        return $allowed;
    }
}
