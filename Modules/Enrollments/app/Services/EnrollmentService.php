<?php

namespace Modules\Enrollments\Services;

use App\Contracts\EnrollmentKeyHasherInterface;
use App\Exceptions\BusinessException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Modules\Auth\Models\User;
use Modules\Enrollments\Contracts\Repositories\EnrollmentRepositoryInterface;
use Modules\Enrollments\DTOs\CreateEnrollmentDTO;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Enrollments\Events\EnrollmentCreated;
use Modules\Enrollments\Mail\AdminEnrollmentNotificationMail;
use Modules\Enrollments\Mail\StudentEnrollmentActiveMail;
use Modules\Enrollments\Mail\StudentEnrollmentApprovedMail;
use Modules\Enrollments\Mail\StudentEnrollmentDeclinedMail;
use Modules\Enrollments\Mail\StudentEnrollmentPendingMail;
use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Models\Course;

class EnrollmentService
{
    public function __construct(
        private EnrollmentRepositoryInterface $repository,
        private EnrollmentKeyHasherInterface $keyHasher
    ) {}

    // Note: Global paginate removed - use paginateByCourse, paginateByCourseIds, or paginateByUser instead

    /**
     * Get paginated enrollments by course.
     * Spatie Query Builder reads filter/sort from request.
     */
    public function paginateByCourse(int $courseId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateByCourse($courseId, $perPage);
    }

    /**
     * Get paginated enrollments by course IDs.
     * Spatie Query Builder reads filter/sort from request.
     */
    public function paginateByCourseIds(array $courseIds, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateByCourseIds($courseIds, $perPage);
    }

    /**
     * Get paginated enrollments by user.
     * Spatie Query Builder reads filter/sort from request.
     */
    public function paginateByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateByUser($userId, $perPage);
    }

    /**
     * Find enrollment by ID.
     */
    public function findById(int $id): ?Enrollment
    {
        return $this->repository->findById($id);
    }

    /**
     * Find enrollment by course and user.
     */
    public function findByCourseAndUser(int $courseId, int $userId): ?Enrollment
    {
        return $this->repository->findByCourseAndUser($courseId, $userId);
    }

    /**
     * Enroll user to a course.
     *
     * @return array{enrollment: Enrollment, status: string, message: string}
     *
     * @throws BusinessException
     */
    public function enroll(Course $course, User $user, CreateEnrollmentDTO $dto): array
    {
        $existing = $this->repository->findByCourseAndUser($course->id, $user->id);

        if ($existing && in_array($existing->status, [EnrollmentStatus::Active, EnrollmentStatus::Pending], true)) {
            throw new BusinessException('Anda sudah terdaftar pada course ini.', ['course' => 'Anda sudah terdaftar pada course ini.']);
        }

        $enrollment = $existing ?? new Enrollment([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        [$status, $message] = $this->determineStatusAndMessage($course, $dto);

        $enrollment->status = EnrollmentStatus::from($status);
        $enrollment->enrolled_at = $enrollment->enrolled_at ?? Carbon::now();

        if ($status !== EnrollmentStatus::Completed->value) {
            $enrollment->completed_at = null;
        }

        $enrollment->save();

        $freshEnrollment = $enrollment->fresh(['course:id,title,slug,code', 'user:id,name,email']);

        EnrollmentCreated::dispatch($freshEnrollment);
        $this->sendEnrollmentEmails($freshEnrollment, $course, $user, $status);

        return [
            'enrollment' => $freshEnrollment,
            'status' => $status,
            'message' => $message,
        ];
    }

    /**
     * Cancel pending enrollment request.
     *
     * @throws BusinessException
     */
    public function cancel(Enrollment $enrollment): Enrollment
    {
        if ($enrollment->status !== EnrollmentStatus::Pending) {
            throw new BusinessException(
                'Hanya enrollment dengan status pending yang dapat dibatalkan.',
                ['enrollment' => 'Hanya enrollment dengan status pending yang dapat dibatalkan.']
            );
        }

        $enrollment->status = EnrollmentStatus::Cancelled;
        $enrollment->completed_at = null;
        $enrollment->save();

        return $enrollment->fresh(['course:id,title,slug', 'user:id,name,email']);
    }

    /**
     * Withdraw from an active course.
     *
     * @throws BusinessException
     */
    public function withdraw(Enrollment $enrollment): Enrollment
    {
        if ($enrollment->status !== EnrollmentStatus::Active) {
            throw new BusinessException(
                'Hanya enrollment aktif yang dapat mengundurkan diri.',
                ['enrollment' => 'Hanya enrollment aktif yang dapat mengundurkan diri.']
            );
        }

        $enrollment->status = EnrollmentStatus::Cancelled;
        $enrollment->completed_at = null;
        $enrollment->save();

        return $enrollment->fresh(['course:id,title,slug', 'user:id,name,email']);
    }

    /**
     * Approve a pending enrollment.
     *
     * @throws BusinessException
     */
    public function approve(Enrollment $enrollment): Enrollment
    {
        if ($enrollment->status !== EnrollmentStatus::Pending) {
            throw new BusinessException(
                'Hanya permintaan enrollment pending yang dapat disetujui.',
                ['enrollment' => 'Hanya permintaan enrollment pending yang dapat disetujui.']
            );
        }

        $enrollment->status = EnrollmentStatus::Active;
        $enrollment->enrolled_at = Carbon::now();
        $enrollment->completed_at = null;
        $enrollment->save();

        $freshEnrollment = $enrollment->fresh(['course:id,title,slug,code', 'user:id,name,email']);
        $course = $freshEnrollment->course;
        $student = $freshEnrollment->user;

        if ($student && $course) {
            $courseUrl = $this->getCourseUrl($course);
            Mail::to($student->email)->send(new StudentEnrollmentApprovedMail($student, $course, $courseUrl));
        }

        return $freshEnrollment;
    }

    /**
     * Decline a pending enrollment.
     *
     * @throws BusinessException
     */
    public function decline(Enrollment $enrollment): Enrollment
    {
        if ($enrollment->status !== EnrollmentStatus::Pending) {
            throw new BusinessException(
                'Hanya permintaan enrollment pending yang dapat ditolak.',
                ['enrollment' => 'Hanya permintaan enrollment pending yang dapat ditolak.']
            );
        }

        $enrollment->status = EnrollmentStatus::Cancelled;
        $enrollment->completed_at = null;
        $enrollment->save();

        $freshEnrollment = $enrollment->fresh(['course:id,title,slug,code', 'user:id,name,email']);
        $course = $freshEnrollment->course;
        $student = $freshEnrollment->user;

        if ($student && $course) {
            Mail::to($student->email)->send(new StudentEnrollmentDeclinedMail($student, $course));
        }

        return $freshEnrollment;
    }

    /**
     * Remove an enrollment from a course.
     *
     * @throws BusinessException
     */
    public function remove(Enrollment $enrollment): Enrollment
    {
        if (! in_array($enrollment->status, [EnrollmentStatus::Active, EnrollmentStatus::Pending], true)) {
            throw new BusinessException(
                'Hanya enrollment aktif atau pending yang dapat dikeluarkan.',
                ['enrollment' => 'Hanya enrollment aktif atau pending yang dapat dikeluarkan.']
            );
        }

        $enrollment->status = EnrollmentStatus::Cancelled;
        $enrollment->completed_at = null;
        $enrollment->save();

        return $enrollment->fresh(['course:id,title,slug', 'user:id,name,email']);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function determineStatusAndMessage(Course $course, CreateEnrollmentDTO $dto): array
    {
        $type = $course->enrollment_type;

        // Handle both enum and string values
        $typeValue = $type instanceof \Modules\Schemes\Enums\EnrollmentType ? $type->value : ($type ?? 'auto_accept');

        return match ($typeValue) {
            'auto_accept' => ['active', 'Enrol berhasil. Anda sekarang terdaftar pada course ini.'],
            'key_based' => $this->handleKeyBasedEnrollment($course, $dto),
            'approval' => ['pending', 'Permintaan enrollment berhasil dikirim. Menunggu persetujuan.'],
            default => ['active', 'Enrol berhasil.'],
        };
    }

    /**
     * @return array{0: string, 1: string}
     *
     * @throws BusinessException
     */
    private function handleKeyBasedEnrollment(Course $course, CreateEnrollmentDTO $dto): array
    {
        $providedKey = trim((string) ($dto->enrollmentKey ?? ''));

        if ($providedKey === '') {
            throw new BusinessException('Kode enrollment wajib diisi.', ['enrollment_key' => 'Kode enrollment wajib diisi.']);
        }

        if (empty($course->enrollment_key_hash) || ! $this->keyHasher->verify($providedKey, $course->enrollment_key_hash)) {
            throw new BusinessException('Kode enrollment tidak valid.', ['enrollment_key' => 'Kode enrollment tidak valid.']);
        }

        return ['active', 'Enrol berhasil menggunakan kode kunci.'];
    }

    /**
     * Send enrollment notification emails to student and admins/instructors.
     */
    private function sendEnrollmentEmails(Enrollment $enrollment, Course $course, User $student, string $status): void
    {
        if ($status === EnrollmentStatus::Active->value) {
            $courseUrl = $this->getCourseUrl($course);
            Mail::to($student->email)->send(new StudentEnrollmentActiveMail($student, $course, $courseUrl));
        } elseif ($status === EnrollmentStatus::Pending->value) {
            Mail::to($student->email)->send(new StudentEnrollmentPendingMail($student, $course));
        }

        $this->notifyCourseManagers($enrollment, $course, $student);
    }

    /**
     * Notify all course managers (admins and instructor) about new enrollment.
     */
    private function notifyCourseManagers(Enrollment $enrollment, Course $course, User $student): void
    {
        $managers = $this->getCourseManagers($course);
        $enrollmentsUrl = $this->getEnrollmentsUrl($course);

        foreach ($managers as $manager) {
            if ($manager && $manager->email) {
                Mail::to($manager->email)->send(
                    new AdminEnrollmentNotificationMail($manager, $student, $course, $enrollment, $enrollmentsUrl)
                );
            }
        }
    }

    /**
     * Get all course managers (instructor + admins).
     *
     * @return array<int, User>
     */
    private function getCourseManagers(Course $course): array
    {
        $managers = [];
        $managerIds = [];

        $course = $course->fresh(['instructor', 'admins']);

        if ($course->instructor_id && $course->instructor) {
            $instructor = $course->instructor;
            $managers[] = $instructor;
            $managerIds[] = $instructor->id;
        }

        foreach ($course->admins as $admin) {
            if ($admin && ! in_array($admin->id, $managerIds, true)) {
                $managers[] = $admin;
                $managerIds[] = $admin->id;
            }
        }

        return $managers;
    }

    /**
     * Generate course URL for frontend.
     */
    private function getCourseUrl(Course $course): string
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));

        return rtrim($frontendUrl, '/').'/courses/'.$course->slug;
    }

    /**
     * Generate enrollments management URL for frontend.
     */
    private function getEnrollmentsUrl(Course $course): string
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));

        return rtrim($frontendUrl, '/').'/courses/'.$course->slug.'/enrollments';
    }
}
