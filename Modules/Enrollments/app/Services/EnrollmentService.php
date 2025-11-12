<?php

namespace Modules\Enrollments\Services;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Models\Course;
use Modules\Auth\Models\User;

class EnrollmentService
{
    /**
     * @param  \Modules\Schemes\Models\Course  $course
     * @param  \Modules\Auth\Models\User  $user
     * @param  array<string, mixed>  $payload
     * @return array{enrollment: Enrollment, status: string, message: string}
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function enroll(Course $course, User $user, array $payload = []): array
    {
        $existing = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing && in_array($existing->status, ['active', 'pending'], true)) {
            throw ValidationException::withMessages([
                'course' => 'Anda sudah terdaftar pada course ini.',
            ]);
        }

        $enrollment = $existing ?? new Enrollment([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'progress_percent' => 0,
        ]);

        [$status, $message] = $this->determineStatusAndMessage($course, $payload);

        $enrollment->status = $status;
        $enrollment->progress_percent = $status === 'active' ? $enrollment->progress_percent : 0;
        $enrollment->enrolled_at = $status === 'active' ? Carbon::now() : null;

        if ($status !== 'completed') {
            $enrollment->completed_at = null;
        }

        $enrollment->save();

        return [
            'enrollment' => $enrollment->fresh(['course:id,title,slug', 'user:id,name,email']),
            'status' => $status,
            'message' => $message,
        ];
    }

    /**
     * Cancel pending enrollment request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function cancel(Enrollment $enrollment): Enrollment
    {
        if ($enrollment->status !== 'pending') {
            throw ValidationException::withMessages([
                'enrollment' => 'Hanya enrolment dengan status pending yang dapat dibatalkan.',
            ]);
        }

        $enrollment->status = 'cancelled';
        $enrollment->enrolled_at = null;
        $enrollment->completed_at = null;
        $enrollment->save();

        return $enrollment->fresh(['course:id,title,slug', 'user:id,name,email']);
    }

    /**
     * Withdraw from an active course.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function withdraw(Enrollment $enrollment): Enrollment
    {
        if ($enrollment->status !== 'active') {
            throw ValidationException::withMessages([
                'enrollment' => 'Hanya enrolment aktif yang dapat mengundurkan diri.',
            ]);
        }

        $enrollment->status = 'cancelled';
        $enrollment->completed_at = null;
        $enrollment->save();

        return $enrollment->fresh(['course:id,title,slug', 'user:id,name,email']);
    }

    /**
     * Approve a pending enrollment.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function approve(Enrollment $enrollment): Enrollment
    {
        if ($enrollment->status !== 'pending') {
            throw ValidationException::withMessages([
                'enrollment' => 'Hanya permintaan enrolment pending yang dapat disetujui.',
            ]);
        }

        $enrollment->status = 'active';
        $enrollment->enrolled_at = Carbon::now();
        $enrollment->completed_at = null;
        $enrollment->save();

        return $enrollment->fresh(['course:id,title,slug', 'user:id,name,email']);
    }

    /**
     * Decline a pending enrollment.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function decline(Enrollment $enrollment): Enrollment
    {
        if ($enrollment->status !== 'pending') {
            throw ValidationException::withMessages([
                'enrollment' => 'Hanya permintaan enrolment pending yang dapat ditolak.',
            ]);
        }

        $enrollment->status = 'cancelled';
        $enrollment->enrolled_at = null;
        $enrollment->completed_at = null;
        $enrollment->save();

        return $enrollment->fresh(['course:id,title,slug', 'user:id,name,email']);
    }

    /**
     * Remove an enrollment from a course.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function remove(Enrollment $enrollment): Enrollment
    {
        if (! in_array($enrollment->status, ['active', 'pending'], true)) {
            throw ValidationException::withMessages([
                'enrollment' => 'Hanya enrolment aktif atau pending yang dapat dikeluarkan.',
            ]);
        }

        $enrollment->status = 'cancelled';
        $enrollment->enrolled_at = null;
        $enrollment->completed_at = null;
        $enrollment->save();

        return $enrollment->fresh(['course:id,title,slug', 'user:id,name,email']);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function determineStatusAndMessage(Course $course, array $payload): array
    {
        $type = $course->enrollment_type ?? 'auto_accept';

        return match ($type) {
            'auto_accept' => ['active', 'Enrol berhasil. Anda sekarang terdaftar pada course ini.'],
            'key_based' => $this->handleKeyBasedEnrollment($course, $payload),
            'approval' => ['pending', 'Permintaan enrolment berhasil dikirim. Menunggu persetujuan.'],
            default => ['active', 'Enrol berhasil.'],
        };
    }

    /**
     * @return array{0: string, 1: string}
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function handleKeyBasedEnrollment(Course $course, array $payload): array
    {
        $providedKey = trim((string) ($payload['enrollment_key'] ?? ''));

        if ($providedKey === '') {
            throw ValidationException::withMessages([
                'enrollment_key' => 'Kode enrolment wajib diisi.',
            ]);
        }

        if (! hash_equals((string) $course->enrollment_key, $providedKey)) {
            throw ValidationException::withMessages([
                'enrollment_key' => 'Kode enrolment tidak valid.',
            ]);
        }

        return ['active', 'Enrol berhasil menggunakan kode kunci.'];
    }
}


