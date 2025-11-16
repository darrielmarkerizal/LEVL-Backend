<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Modules\Auth\Models\User;
use Modules\Schemes\Models\Lesson;

class LessonPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Lesson $lesson)
    {
        if (! $user->hasRole('Admin')) {
            return $this->deny('Hanya admin atau superadmin yang dapat mengubah lesson.');
        }

        $unit = $lesson->unit;
        if (! $unit) {
            return $this->deny('Unit tidak ditemukan.');
        }

        $course = $unit->course;
        if (! $course) {
            return $this->deny('Course tidak ditemukan.');
        }

        // Check if user is instructor or course admin
        if ((int) $course->instructor_id === (int) $user->id) {
            return Response::allow();
        }

        if (method_exists($course, 'hasAdmin') && $course->hasAdmin($user)) {
            return Response::allow();
        }

        return $this->deny('Anda hanya dapat mengubah lesson dari course yang Anda buat atau course yang Anda kelola sebagai admin.');
    }

    public function delete(User $user, Lesson $lesson)
    {
        if (! $user->hasRole('Admin')) {
            return $this->deny('Hanya admin atau superadmin yang dapat menghapus lesson.');
        }

        $unit = $lesson->unit;
        if (! $unit) {
            return $this->deny('Unit tidak ditemukan.');
        }

        $course = $unit->course;
        if (! $course) {
            return $this->deny('Course tidak ditemukan.');
        }

        // Check if user is instructor or course admin
        if ((int) $course->instructor_id === (int) $user->id) {
            return Response::allow();
        }

        if (method_exists($course, 'hasAdmin') && $course->hasAdmin($user)) {
            return Response::allow();
        }

        return $this->deny('Anda hanya dapat menghapus lesson dari course yang Anda buat atau course yang Anda kelola sebagai admin.');
    }
}
