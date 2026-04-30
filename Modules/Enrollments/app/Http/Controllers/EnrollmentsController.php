<?php

declare(strict_types=1);

namespace Modules\Enrollments\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Enrollments\Contracts\Services\EnrollmentServiceInterface;
use Modules\Enrollments\Http\Requests\BulkEnrollmentActionRequest;
use Modules\Enrollments\Http\Resources\EnrollmentResource;
use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Models\Course;

class EnrollmentsController extends Controller
{
    use ApiResponse;
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function __construct(private readonly EnrollmentServiceInterface $service) {}

    public function index(Request $request)
    {
        $user = auth('api')->user();
        $perPage = max(1, (int) $request->query('per_page', 15));

        $paginator = $this->service->listEnrollmentsForIndex($user, $perPage, $request->all());

        $paginator->getCollection()->transform(fn ($item) => new \Modules\Enrollments\Http\Resources\EnrollmentIndexResource($item));

        return $this->paginateResponse($paginator, __('messages.enrollments.list_retrieved'));
    }

    public function indexByCourse(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $perPage = max(1, (int) $request->query('per_page', 15));
        $paginator = $this->service->paginateByCourse($course->id, $perPage, $request->all());

        $paginator->getCollection()->transform(fn ($item) => new EnrollmentResource($item));

        return $this->paginateResponse($paginator, __('messages.enrollments.course_list_retrieved'));
    }

    public function show($id)
    {
        $enrollment = \Spatie\QueryBuilder\QueryBuilder::for(Enrollment::class)
            ->allowedIncludes(['user', 'course'])
            ->with([
                'user',
                'course',
                'courseProgress',
                'unitProgress',
                'assignmentSubmissions',
                'quizSubmissions',
            ])
            ->findOrFail($id);

        $this->authorize('view', $enrollment);

        return $this->success(new EnrollmentResource($enrollment), __('messages.enrollments.retrieved'));
    }

    public function showByCourse(Course $course, $enrollmentId)
    {
        $enrollment = \Spatie\QueryBuilder\QueryBuilder::for(Enrollment::class)
            ->allowedIncludes(['user', 'course'])
            ->with(['user', 'course'])
            ->where('course_id', $course->id)
            ->findOrFail($enrollmentId);

        $this->authorize('view', $enrollment);

        return $this->success(new EnrollmentResource($enrollment), __('messages.enrollments.retrieved'));
    }

    public function activities(Request $request, $enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $this->authorize('view', $enrollment);

        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        $paginator = \Spatie\QueryBuilder\QueryBuilder::for(\Modules\Enrollments\Models\EnrollmentActivity::class)
            ->where('enrollment_id', $enrollmentId)
            ->allowedSorts(['occurred_at', 'created_at', 'event_type'])
            ->allowedFilters(['event_type'])
            ->defaultSort('-occurred_at')
            ->with(['lesson', 'quiz', 'assignment'])
            ->paginate($perPage)
            ->appends($request->query());

        $paginator->getCollection()->transform(fn ($item) => new \Modules\Enrollments\Http\Resources\EnrollmentActivityResource($item));

        return $this->paginateResponse($paginator, __('messages.enrollments.activities_retrieved'));
    }

    public function store(Request $request, Course $course)
    {
        $user = auth('api')->user();

        $result = $this->service->enroll($user, $course, $request->all());

        return $this->created(
            new EnrollmentResource($result['enrollment']),
            $result['message']
        );
    }

    public function createManual(\Modules\Enrollments\Http\Requests\CreateManualEnrollmentRequest $request)
    {
        $actor = auth('api')->user();
        $data = $request->validated();

        $course = Course::where('slug', $data['course_slug'])->firstOrFail();
        $this->authorize('update', $course);

        $result = $this->service->enrollManually($actor, $course, $data);

        return $this->created(
            new EnrollmentResource($result['enrollment']),
            $result['message']
        );
    }

    public function cancel(Request $request, Course $course)
    {
        $user = auth('api')->user();

        $enrollment = $this->service->findEnrollmentForAction($course, $user, $request->all());
        $this->authorize('cancel', $enrollment);

        $updated = $this->service->cancel($enrollment);

        return $this->success(new EnrollmentResource($updated), __('messages.enrollments.cancelled'));
    }

    public function withdraw(Request $request, Course $course)
    {
        $user = auth('api')->user();

        $enrollment = $this->service->findEnrollmentForAction($course, $user, $request->all());
        $this->authorize('withdraw', $enrollment);

        $updated = $this->service->withdraw($enrollment);

        return $this->success(new EnrollmentResource($updated), __('messages.enrollments.withdrawn'));
    }

    public function status(Request $request, Course $course)
    {
        $user = auth('api')->user();

        $result = $this->service->getEnrollmentStatus($course, $user, $request->all());

        if (! $result['found']) {
            return $this->success(
                ['status' => 'not_enrolled', 'enrollment' => null],
                __('messages.enrollments.not_enrolled'),
            );
        }

        $this->authorize('view', $result['enrollment']);

        return $this->success(
            [
                'status' => $result['enrollment']->status,
                'enrollment' => new EnrollmentResource($result['enrollment']),
            ],
            __('messages.enrollments.status_retrieved'),
        );
    }

    public function approve(Enrollment $enrollment)
    {
        $this->authorize('approve', $enrollment);

        $updated = $this->service->approve($enrollment);

        return $this->success(new EnrollmentResource($updated), __('messages.enrollments.approved'));
    }

    public function decline(Enrollment $enrollment)
    {
        $this->authorize('decline', $enrollment);

        $updated = $this->service->decline($enrollment);

        return $this->success(new EnrollmentResource($updated), __('messages.enrollments.rejected'));
    }

    public function remove(Enrollment $enrollment)
    {
        $this->authorize('remove', $enrollment);

        $updated = $this->service->remove($enrollment);

        return $this->success(new EnrollmentResource($updated), __('messages.enrollments.expelled'));
    }

    public function bulkApprove(BulkEnrollmentActionRequest $request)
    {
        $data = $request->validated();
        $actor = auth('api')->user();
        $enrollments = $this->service->getEnrollmentsAuthorizedFor(
            $actor,
            $data['enrollment_ids'],
            'approve',
            [\Modules\Enrollments\Enums\EnrollmentStatus::Pending->value]
        );
        $result = $this->service->bulkApprove($enrollments);

        return $this->success([
            'processed' => EnrollmentResource::collection($result['processed']),
            'failed' => $result['failed'],
        ], __('messages.enrollments.bulk_action_completed'));
    }

    public function bulkDecline(BulkEnrollmentActionRequest $request)
    {
        $data = $request->validated();
        $actor = auth('api')->user();
        $enrollments = $this->service->getEnrollmentsAuthorizedFor(
            $actor,
            $data['enrollment_ids'],
            'decline',
            [\Modules\Enrollments\Enums\EnrollmentStatus::Pending->value]
        );
        $result = $this->service->bulkDecline($enrollments);

        return $this->success([
            'processed' => EnrollmentResource::collection($result['processed']),
            'failed' => $result['failed'],
        ], __('messages.enrollments.bulk_action_completed'));
    }

    public function bulkRemove(BulkEnrollmentActionRequest $request)
    {
        $data = $request->validated();
        $actor = auth('api')->user();
        $enrollments = $this->service->getEnrollmentsAuthorizedFor(
            $actor,
            $data['enrollment_ids'],
            'remove',
            [
                \Modules\Enrollments\Enums\EnrollmentStatus::Pending->value,
                \Modules\Enrollments\Enums\EnrollmentStatus::Active->value,
            ]
        );
        $result = $this->service->bulkRemove($enrollments);

        return $this->success([
            'processed' => EnrollmentResource::collection($result['processed']),
            'failed' => $result['failed'],
        ], __('messages.enrollments.bulk_action_completed'));
    }

    public function listInvitations(Request $request)
    {
        $student = auth('api')->user();
        $perPage = max(1, (int) $request->query('per_page', 15));

        $paginator = $this->service->getPendingEnrollmentsForStudent($student, $perPage);

        $paginator->getCollection()->transform(fn ($item) => new \Modules\Enrollments\Http\Resources\EnrollmentInvitationResource($item));

        return $this->paginateResponse($paginator, __('messages.enrollments.invitations_retrieved'));
    }

    public function showInvitation($enrollmentId)
    {
        $student = auth('api')->user();

        $enrollment = $this->service->getPendingEnrollmentForStudent($student, (int) $enrollmentId);

        if (! $enrollment) {
            return $this->error(__('messages.enrollments.invitation_not_found'), [], 404);
        }

        return $this->success(
            new \Modules\Enrollments\Http\Resources\EnrollmentInvitationResource($enrollment),
            __('messages.enrollments.invitation_retrieved')
        );
    }

    public function acceptInvitation($enrollmentId)
    {
        $student = auth('api')->user();

        $enrollment = $this->service->getPendingEnrollmentForStudent($student, (int) $enrollmentId);

        if (! $enrollment) {
            return $this->error(__('messages.enrollments.invitation_not_found'), [], 404);
        }

        try {
            $approved = $this->service->approve($enrollment);

            return $this->success(
                new \Modules\Enrollments\Http\Resources\EnrollmentInvitationResource($approved),
                __('messages.enrollments.invitation_accepted')
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), [], 400);
        }
    }

    public function declineInvitation($enrollmentId)
    {
        $student = auth('api')->user();

        $enrollment = $this->service->getPendingEnrollmentForStudent($student, (int) $enrollmentId);

        if (! $enrollment) {
            return $this->error(__('messages.enrollments.invitation_not_found'), [], 404);
        }

        try {
            $declined = $this->service->decline($enrollment);

            return $this->success(
                new \Modules\Enrollments\Http\Resources\EnrollmentInvitationResource($declined),
                __('messages.enrollments.invitation_declined')
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), [], 400);
        }
    }
}
