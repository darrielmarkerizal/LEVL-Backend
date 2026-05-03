<?php

declare(strict_types=1);

namespace Modules\Learning\Policies;

use Modules\Auth\Models\User;
use Modules\Learning\Models\QuizSubmission;
use Modules\Schemes\Traits\ValidatesEnrollment;

class QuizSubmissionPolicy
{
    use ValidatesEnrollment;

    public function view(?User $user, QuizSubmission $submission): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Superadmin') || $user->hasRole('Admin') || $user->hasRole('Instructor')) {
            return true;
        }

        
        if ($submission->user_id !== $user->id) {
            return false;
        }

        
        if ($user->hasRole('Student')) {
            $submission->loadMissing('quiz.unit.course');
            $course = $submission->quiz?->unit?->course;

            if (! $course) {
                return false;
            }

            return $this->isEnrolled($course);
        }

        return true;
    }

    public function update(User $user, QuizSubmission $submission): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        if ($submission->user_id !== $user->id) {
            return false;
        }

        if ($submission->status?->value !== 'draft') {
            return false;
        }

        if ($user->hasRole('Student')) {
            $submission->loadMissing('quiz.unit.course');
            $course = $submission->quiz?->unit?->course;

            if (! $course) {
                return false;
            }

            return $this->isEnrolled($course);
        }

        return true;
    }

    public function takeover(User $user, QuizSubmission $submission): bool
    {
        if ($submission->user_id !== $user->id) {
            return false;
        }

        if ($submission->status?->value !== 'draft') {
            return false;
        }

        if ($user->hasRole('Student')) {
            $submission->loadMissing('quiz.unit.course');
            $course = $submission->quiz?->unit?->course;

            if (! $course) {
                return false;
            }

            return $this->isEnrolled($course);
        }

        return true;
    }
}
