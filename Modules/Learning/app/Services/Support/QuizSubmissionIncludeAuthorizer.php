<?php

declare(strict_types=1);

namespace Modules\Learning\Services\Support;

use Modules\Auth\Models\User;
use Modules\Learning\Models\QuizSubmission;

class QuizSubmissionIncludeAuthorizer
{
    private const OWNER_INCLUDES = [
        'answers',
        'quiz',
        'user',
    ];

    private const MANAGER_INCLUDES = [
        'answers',
        'quiz',
        'user',
    ];

    public function getAllowedIncludesForQueryBuilder(?User $user, QuizSubmission $submission): array
    {
        if (! $user) {
            return [];
        }

        if ($this->isOwner($user, $submission)) {
            return self::OWNER_INCLUDES;
        }

        if ($this->isManager($user, $submission)) {
            return self::MANAGER_INCLUDES;
        }

        return [];
    }

    private function isOwner(User $user, QuizSubmission $submission): bool
    {
        return $submission->user_id === $user->id;
    }

    private function isManager(User $user, QuizSubmission $submission): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        $quiz = $submission->quiz;
        $assignable = $quiz->assignable;

        if (! $assignable) {
            return false;
        }

        if ($assignable instanceof \Modules\Schemes\Models\Course) {
            return $this->isCourseManager($user, $assignable);
        }

        if ($assignable instanceof \Modules\Schemes\Models\Unit) {
            return $this->isCourseManager($user, $assignable->course);
        }

        if ($assignable instanceof \Modules\Schemes\Models\Lesson) {
            return $this->isCourseManager($user, $assignable->unit->course);
        }

        return false;
    }

    private function isCourseManager(User $user, $course): bool
    {
        if ($user->hasRole('Admin')) {
            return $course->admins()->where('user_id', $user->id)->exists();
        }

        if ($user->hasRole('Instructor')) {
            return $course->instructor_id === $user->id;
        }

        return false;
    }
}
