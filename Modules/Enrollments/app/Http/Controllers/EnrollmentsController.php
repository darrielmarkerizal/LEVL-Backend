<?php

namespace Modules\Enrollments\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Enrollments\DTOs\CreateEnrollmentDTO;
use Modules\Enrollments\Http\Requests\EnrollRequest;
use Modules\Enrollments\Models\Enrollment;
use Modules\Enrollments\Services\EnrollmentService;
use Modules\Schemes\Models\Course;

class EnrollmentsController extends Controller
{
    use ApiResponse;
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function __construct(private EnrollmentService $service) {}

    public function index(Request $request)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        if (! $user->hasRole('Superadmin')) {
            return $this->error(__('messages.enrollments.no_view_all_access'), [], 403);
        }

        return $this->error(__('messages.endpoint_unavailable'), 501);
    }

    public function indexByCourse(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $perPage = max(1, (int) $request->query('per_page', 15));
        $paginator = $this->service->paginateByCourse($course->id, $perPage);

        return $this->paginateResponse($paginator, __('messages.enrollments.course_list_retrieved'));
    }

    public function indexManaged(Request $request)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        if ($user->hasRole('Superadmin')) {
            return $this->index($request);
        }

        // Middleware role:Superadmin|Admin|Instructor handles access checks

        $perPage = max(1, (int) $request->query('per_page', 15));
        $courseSlug = $request->input('filter.course_slug');

        $result = $this->service->getManagedEnrollments($user, $perPage, $courseSlug);

        if (! $result['found']) {
            return $this->error(
                __('messages.enrollments.course_not_managed'),
                404,
            );
        }

        return $this->paginateResponse($result['paginator'], __('messages.enrollments.list_retrieved'));
    }

    public function enroll(EnrollRequest $request, Course $course)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        // Access control handled by role:Student middleware

        $dto = CreateEnrollmentDTO::fromRequest([
            'course_id' => $course->id,
            'enrollment_key' => $request->input('enrollment_key'),
        ]);

        $result = $this->service->enroll($course, $user, $dto);

        return $this->success(['enrollment' => $result['enrollment']], $result['message']);
    }

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
            return $this->error(__('messages.enrollments.request_not_found'), 404);
        }

        $this->authorize('cancel', $enrollment);

        $updated = $this->service->cancel($enrollment);

        return $this->success(['enrollment' => $updated], __('messages.enrollments.cancelled'));
    }

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
            return $this->error(__('messages.enrollments.not_found'), 404);
        }

        $this->authorize('withdraw', $enrollment);

        $updated = $this->service->withdraw($enrollment);

        return $this->success(
            ['enrollment' => $updated],
            __('messages.enrollments.withdrawn'),
        );
    }

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
                __('messages.enrollments.not_enrolled'),
            );
        }

        $this->authorize('view', $enrollment);

        $enrollmentData = $enrollment->fresh(['course:id,title,slug', 'user:id,name,email']);

        return $this->success(
            [
                'status' => $enrollmentData->status,
                'enrollment' => $enrollmentData,
            ],
            __('messages.enrollments.status_retrieved'),
        );
    }

    public function approve(Enrollment $enrollment)
    {
        $this->authorize('approve', $enrollment);

        $updated = $this->service->approve($enrollment);

        return $this->success(['enrollment' => $updated], __('messages.enrollments.approved'));
    }

    public function decline(Enrollment $enrollment)
    {
        $this->authorize('decline', $enrollment);

        $updated = $this->service->decline($enrollment);

        return $this->success(['enrollment' => $updated], __('messages.enrollments.rejected'));
    }

    public function remove(Enrollment $enrollment)
    {
        $this->authorize('remove', $enrollment);

        $updated = $this->service->remove($enrollment);

        return $this->success(['enrollment' => $updated], __('messages.enrollments.expelled'));
    }
}
