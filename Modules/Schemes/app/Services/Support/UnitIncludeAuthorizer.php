<?php

declare(strict_types=1);

namespace Modules\Schemes\Services\Support;

use Modules\Auth\Models\User;
use Modules\Schemes\Models\Unit;

class UnitIncludeAuthorizer
{
    private const PUBLIC_INCLUDES = [
        'course',
    ];

    private const ENROLLED_STUDENT_INCLUDES = [
        'lessons',
    ];

    private const MANAGER_INCLUDES = [
        'lessons.blocks',
    ];

    public function getAllowedIncludesForQueryBuilder(?User $user, Unit $unit): array
    {
        $allowed = self::PUBLIC_INCLUDES;

        if ($user) {
            $course = $unit->course;

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
            return true; // Admins have global access to all courses
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
}
