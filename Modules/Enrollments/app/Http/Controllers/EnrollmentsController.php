<?php

namespace Modules\Enrollments\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Enrollments\DTOs\CreateEnrollmentDTO;
use Modules\Enrollments\Models\Enrollment;
use Modules\Enrollments\Services\EnrollmentService;
use Modules\Schemes\Models\Course;

/**
 * @tags Pendaftaran Kursus
 */
class EnrollmentsController extends Controller
{
    use ApiResponse;

    public function __construct(private EnrollmentService $service) {}

    /**
     * @summary Daftar Semua Pendaftaran (Superadmin)
     */
    public function index(Request $request)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        if (! $user->hasRole('Superadmin')) {
            return $this->error('Anda tidak memiliki akses untuk melihat seluruh enrollment.', 403);
        }

        // Note: paginate method needs to be added if used
        return $this->error('Endpoint tidak tersedia untuk saat ini.', 501);
    }

    /**
     * @summary Daftar Pendaftaran per Kursus
     */
    public function indexByCourse(Request $request, Course $course)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        if (! $this->userCanManageCourse($user, $course)) {
            return $this->error('Anda tidak memiliki akses untuk melihat enrollment course ini.', 403);
        }

        $perPage = max(1, (int) $request->query('per_page', 15));
        $paginator = $this->service->paginateByCourse($course->id, $perPage);

        return $this->paginateResponse($paginator, 'Daftar enrollment course berhasil diambil.');
    }

    /**
     * @summary Daftar Pendaftaran yang Dikelola
     */
    public function indexManaged(Request $request)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        if ($user->hasRole('Superadmin')) {
            return $this->index($request);
        }

        if (! $user->hasRole('Admin') && ! $user->hasRole('Instructor')) {
            return $this->error('Anda tidak memiliki akses untuk melihat enrollment ini.', 403);
        }

        $courses = Course::query()
            ->select(['id', 'slug', 'title'])
            ->where(function ($query) use ($user) {
                $query
                    ->where('instructor_id', $user->id)
                    ->orWhereHas('admins', function ($adminQuery) use ($user) {
                        $adminQuery->where('user_id', $user->id);
                    });
            })
            ->get();

        $courseIds = $courses->pluck('id')->all();
        $perPage = max(1, (int) $request->query('per_page', 15));

        // Handle course_slug filter
        $courseSlug = $request->input('filter.course_slug');
        if ($courseSlug) {
            $course = $courses->firstWhere('slug', $courseSlug);
            if (! $course) {
                return $this->error(
                    'Course tidak ditemukan atau tidak berada di bawah pengelolaan Anda.',
                    404,
                );
            }
            $paginator = $this->service->paginateByCourse($course->id, $perPage);
        } else {
            $paginator = $this->service->paginateByCourseIds($courseIds, $perPage);
        }

        return $this->paginateResponse($paginator, 'Daftar enrollment berhasil diambil.');
    }

    /**
     * @summary Daftar ke Kursus
     */
    public function enroll(Request $request, Course $course)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        if (! $user->hasRole('Student')) {
            return $this->error('Hanya peserta yang dapat melakukan enrollment.', 403);
        }

        $request->validate([
            'enrollment_key' => ['nullable', 'string', 'max:100'],
        ]);

        $dto = CreateEnrollmentDTO::fromRequest([
            'course_id' => $course->id,
            'enrollment_key' => $request->input('enrollment_key'),
        ]);

        $result = $this->service->enroll($course, $user, $dto);

        return $this->success(
            ['enrollment' => $result['enrollment']],
            $result['message'],
        );
    }

    /**
     * @summary Batalkan Permintaan Pendaftaran
     */
    public function cancel(Request $request, Course $course)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        $targetUserId = (int) $user->id;
        if ($user->hasRole('Superadmin')) {
            $targetUserId = (int) $request->input('user_id', $user->id);
        }

        $enrollment = $this->service->findByCourseAndUser($course->id, $targetUserId);

        if (! $enrollment) {
            return $this->error('Permintaan enrollment tidak ditemukan untuk course ini.', 404);
        }

        if (! $this->canModifyEnrollment($user, $enrollment)) {
            return $this->error('Anda tidak memiliki akses untuk membatalkan enrollment ini.', 403);
        }

        $updated = $this->service->cancel($enrollment);

        return $this->success(['enrollment' => $updated], 'Permintaan enrollment berhasil dibatalkan.');
    }

    /**
     * @summary Undur Diri dari Kursus
     */
    public function withdraw(Request $request, Course $course)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        $targetUserId = (int) $user->id;
        if ($user->hasRole('Superadmin')) {
            $targetUserId = (int) $request->input('user_id', $user->id);
        }

        $enrollment = $this->service->findByCourseAndUser($course->id, $targetUserId);

        if (! $enrollment) {
            return $this->error('Enrollment tidak ditemukan untuk course ini.', 404);
        }

        if (! $this->canModifyEnrollment($user, $enrollment)) {
            return $this->error(
                'Anda tidak memiliki akses untuk mengundurkan diri dari enrollment ini.',
                403,
            );
        }

        $updated = $this->service->withdraw($enrollment);

        return $this->success(
            ['enrollment' => $updated],
            'Anda berhasil mengundurkan diri dari course.',
        );
    }

    /**
     * @summary Status Pendaftaran
     */
    public function status(Request $request, Course $course)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        $targetUserId = (int) $user->id;
        if ($user->hasRole('Superadmin')) {
            $targetUserId = (int) $request->query('user_id', $user->id);
        }

        $enrollment = $this->service->findByCourseAndUser($course->id, $targetUserId);

        if (! $enrollment) {
            return $this->success(
                [
                    'status' => 'not_enrolled',
                    'enrollment' => null,
                ],
                'Anda belum terdaftar pada course ini.',
            );
        }

        if (
            ! $this->canModifyEnrollment($user, $enrollment) &&
            ! $this->userCanManageCourse($user, $course)
        ) {
            return $this->error('Anda tidak memiliki akses untuk melihat status enrollment ini.', 403);
        }

        $enrollmentData = $enrollment->fresh(['course:id,title,slug', 'user:id,name,email']);

        return $this->success(
            [
                'status' => $enrollmentData->status,
                'enrollment' => $enrollmentData,
            ],
            'Status enrollment berhasil diambil.',
        );
    }

    /**
     * @summary Setujui Pendaftaran
     */
    public function approve(Enrollment $enrollment)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        $enrollment->loadMissing('course');

        if (! $enrollment->course || ! $this->userCanManageCourse($user, $enrollment->course)) {
            return $this->error('Anda tidak memiliki akses untuk menyetujui enrollment ini.', 403);
        }

        $updated = $this->service->approve($enrollment);

        return $this->success(['enrollment' => $updated], 'Permintaan enrollment disetujui.');
    }

    /**
     * @summary Tolak Pendaftaran
     */
    public function decline(Enrollment $enrollment)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        $enrollment->loadMissing('course');

        if (! $enrollment->course || ! $this->userCanManageCourse($user, $enrollment->course)) {
            return $this->error('Anda tidak memiliki akses untuk menolak enrollment ini.', 403);
        }

        $updated = $this->service->decline($enrollment);

        return $this->success(['enrollment' => $updated], 'Permintaan enrollment ditolak.');
    }

    /**
     * @summary Hapus Pendaftaran dari Kursus
     */
    public function remove(Enrollment $enrollment)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        $enrollment->loadMissing('course');

        if (! $enrollment->course || ! $this->userCanManageCourse($user, $enrollment->course)) {
            return $this->error(
                'Anda tidak memiliki akses untuk mengeluarkan peserta dari course ini.',
                403,
            );
        }

        $updated = $this->service->remove($enrollment);

        return $this->success(['enrollment' => $updated], 'Peserta berhasil dikeluarkan dari course.');
    }

    private function canModifyEnrollment($user, Enrollment $enrollment): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        return (int) $enrollment->user_id === (int) $user->id;
    }

    private function userCanManageCourse($user, Course $course): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        if ($user->hasRole('Admin') || $user->hasRole('Instructor')) {
            if ((int) $course->instructor_id === (int) $user->id) {
                return true;
            }

            if (method_exists($course, 'hasAdmin') && $course->hasAdmin($user)) {
                return true;
            }
        }

        return false;
    }
}
