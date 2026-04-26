<?php

declare(strict_types=1);

namespace Modules\Learning\Policies;

use Modules\Auth\Models\User;
use Modules\Learning\Models\Quiz;
use Modules\Schemes\Traits\ValidatesEnrollment;

class QuizPolicy
{
    use ValidatesEnrollment;

    private function resolveCourseFromQuiz(Quiz $quiz): ?\Modules\Schemes\Models\Course
    {
        $courseId = $quiz->getCourseId();
        if (! $courseId) {
            return null;
        }

        return \Modules\Schemes\Models\Course::find($courseId);
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Quiz $quiz): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Superadmin') || $user->hasRole('Admin')) {
            return true;
        }

        $course = $this->resolveCourseFromQuiz($quiz);
        if (! $course) {
            return false;
        }

        if ($user->hasRole('Instructor')) {
            return $course->isAssignedInstructor($user);
        }

        if ($user->hasRole('Student')) {
            return $this->isEnrolled($course);
        }

        return false;
    }

    public function create(User $user, \Modules\Schemes\Models\Course $course): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $course->isAssignedInstructor($user);
    }

    public function update(User $user, Quiz $quiz): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        $course = $this->resolveCourseFromQuiz($quiz);
        if (! $course) {
            return false;
        }

        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $course->isAssignedInstructor($user);
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $this->update($user, $quiz);
    }

    public function viewSubmissions(User $user, Quiz $quiz): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        $course = $this->resolveCourseFromQuiz($quiz);
        if (! $course) {
            return false;
        }

        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $course->isAssignedInstructor($user);
    }

    public function takeQuiz(User $user, Quiz $quiz): bool
    {
        if (! $quiz->isAvailable()) {
            return false;
        }

        $course = $this->resolveCourseFromQuiz($quiz);
        if (! $course) {
            return false;
        }

        if ($user->hasRole('Superadmin') || $user->hasRole('Admin') || $user->hasRole('Instructor')) {
            return true;
        }

        if ($user->hasRole('Student')) {
            return $this->getActiveEnrollment($course) !== null;
        }

        return false;
    }
}
