<?php

declare(strict_types=1);

namespace Modules\Learning\Policies;

use Modules\Auth\Models\User;
use Modules\Learning\Models\Assignment;

class AssignmentPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Assignment $assignment): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Superadmin') || $user->hasRole('Admin')) {
            return true;
        }

        $courseId = $assignment->getCourseId();
        if (! $courseId) {
            return false;
        }

        if ($user->hasRole('Instructor')) {
            $assignment->loadMissing('unit.course');
            $course = $assignment->unit?->course;

            return $course && $course->isAssignedInstructor($user);
        }

        if ($user->hasRole('Student')) {
            return \Modules\Enrollments\Models\Enrollment::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->whereIn('status', ['active', 'completed'])
                ->exists();
        }

        return false;
    }

    public function resolveCourseFromAssignment(Assignment $assignment): ?\Modules\Schemes\Models\Course
    {
        $courseId = $assignment->getCourseId();
        if (! $courseId) {
            return null;
        }

        return \Modules\Schemes\Models\Course::find($courseId);
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

    public function update(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        $course = $this->resolveCourseFromAssignment($assignment);
        if (! $course) {
            return false;
        }

        
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $course->isAssignedInstructor($user);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        $course = $this->resolveCourseFromAssignment($assignment);
        if (! $course) {
            return false;
        }

        
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $course->isAssignedInstructor($user);
    }

    public function publish(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }

    public function duplicate(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        $courseId = $assignment->getCourseId();

        if (! $courseId) {
            return false;
        }

        $course = \Modules\Schemes\Models\Course::find($courseId);

        if (! $course) {
            return false;
        }

        
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $course->isAssignedInstructor($user);
    }

    public function listQuestions(User $user, Assignment $assignment): bool
    {
        
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        
        if ($user->hasRole('Student')) {
            return false;
        }

        $courseId = $assignment->getCourseId();

        if (! $courseId) {
            return false;
        }

        $course = \Modules\Schemes\Models\Course::find($courseId);

        if (! $course) {
            return false;
        }

        
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $course->isAssignedInstructor($user);
    }
}
