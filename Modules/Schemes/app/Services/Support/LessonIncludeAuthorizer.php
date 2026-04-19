<?php

declare(strict_types=1);

namespace Modules\Schemes\Services\Support;

use Modules\Auth\Models\User;
use Modules\Schemes\Models\Lesson;

class LessonIncludeAuthorizer
{
    private const PUBLIC_INCLUDES = [
        'unit',
    ];

    private const ENROLLED_STUDENT_INCLUDES = [
        'blocks',
    ];

    private const MANAGER_INCLUDES = [];

    public function getAllowedIncludesForQueryBuilder(?User $user, Lesson $lesson): array
    {
        $allowed = self::PUBLIC_INCLUDES;

        if ($user) {
            $course = $lesson->unit->course;

            if ($this->isManager($user, $course)) {
                $allowed = array_merge($allowed, self::ENROLLED_STUDENT_INCLUDES, self::MANAGER_INCLUDES);
            } elseif ($this->isEnrolledStudent($user, $course)) {
                $allowed = array_merge($allowed, self::ENROLLED_STUDENT_INCLUDES);
            }
        }

        return $allowed;
    }

    private function isManager(User $user, $course): bool
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

    private function isEnrolledStudent(User $user, $course): bool
    {
        return $course->enrollments()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    public function canInclude(string $include, ?User $user, Lesson $lesson): bool
    {
        $allowed = $this->getAllowedIncludesForQueryBuilder($user, $lesson);

        return in_array($include, $allowed, true);
    }
}
