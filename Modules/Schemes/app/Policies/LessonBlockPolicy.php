<?php

declare(strict_types=1);

namespace Modules\Schemes\Policies;

use Modules\Auth\Models\User;
use Modules\Schemes\Models\LessonBlock;

class LessonBlockPolicy
{
    public function view(User $user, LessonBlock $block): bool
    {
        $lesson = $block->lesson;
        if (! $lesson) {
            return false;
        }

        $course = $lesson->unit?->course;
        if (! $course) {
            return false;
        }

        if ($user->hasRole('Student')) {
            return \Modules\Enrollments\Models\Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->whereIn('status', ['active', 'completed'])
                ->exists();
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Superadmin') || $user->hasRole('Admin') || $user->hasRole('Instructor');
    }

    public function update(User $user, LessonBlock $block): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        $course = $block->lesson?->unit?->course;
        if (! $course) {
            return false;
        }

        // Admin can update all lesson blocks
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Instructor can update lesson blocks in their courses
        return $user->hasRole('Instructor') && $course->instructor_id === $user->id;
    }

    public function delete(User $user, LessonBlock $block): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        $course = $block->lesson?->unit?->course;
        if (! $course) {
            return false;
        }

        // Admin can delete all lesson blocks
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Instructor can delete lesson blocks in their courses
        return $user->hasRole('Instructor') && $course->instructor_id === $user->id;
    }
}
