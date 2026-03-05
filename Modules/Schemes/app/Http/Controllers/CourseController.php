<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Controllers;

use App\Exceptions\DuplicateResourceException;
use App\Support\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Schemes\Contracts\Services\CourseServiceInterface;
use Modules\Schemes\Http\Requests\CourseRequest;
use Modules\Schemes\Http\Requests\PublishCourseRequest;
use Modules\Schemes\Http\Resources\CourseResource;
use Modules\Schemes\Models\Course;

class CourseController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    public function __construct(
        private readonly CourseServiceInterface $service,
    ) {}

    public function index(Request $request)
    {
        $paginator = $this->service->listForIndex(
            $request->all(),
            (int) $request->query('per_page', 15)
        );
        $paginator->getCollection()->transform(fn ($course) => new \Modules\Schemes\Http\Resources\CourseIndexResource($course));

        return $this->paginateResponse($paginator, 'messages.courses.list_retrieved');
    }

    public function store(CourseRequest $request)
    {
        $data = $request->validated();
        $actor = auth('api')->user();

        try {
            $course = $this->service->create($data, $actor, $request->allFiles());

            return $this->created(new CourseResource($course), __('messages.courses.created'));
        } catch (DuplicateResourceException $e) {
            return $this->validationError($e->getErrors());
        }
    }

    public function show(Course $course)
    {
        $this->authorize('view', $course);

        $courseWithIncludes = $this->service->findBySlugWithIncludes($course->slug);

        if (! $courseWithIncludes) {
            return $this->notFound(__('messages.courses.not_found'));
        }

        return $this->success(new CourseResource($courseWithIncludes));
    }

    public function update(CourseRequest $request, Course $course)
    {
        $courseWithAdmins = $this->service->findWithAdmins($course->id);
        $this->authorize('update', $courseWithAdmins);

        try {
            $updated = $this->service->update($course->id, $request->validated(), $request->allFiles());

            return $this->success(new CourseResource($updated), __('messages.courses.updated'));
        } catch (DuplicateResourceException $e) {
            return $this->validationError($e->getErrors());
        }
    }

    public function destroy(Course $course)
    {
        $courseWithAdmins = $this->service->findWithAdmins($course->id);
        $this->authorize('delete', $courseWithAdmins);
        $this->service->delete($course->id);

        return $this->success([], __('messages.courses.deleted'));
    }

    public function publish(Course $course)
    {
        $courseWithAdmins = $this->service->findWithAdmins($course->id);
        $this->authorize('update', $courseWithAdmins);

        $updated = $this->service->publish($course->id);

        return $this->success(new CourseResource($updated), __('messages.courses.published'));
    }

    public function unpublish(Course $course)
    {
        $courseWithAdmins = $this->service->findWithAdmins($course->id);
        $this->authorize('update', $courseWithAdmins);

        $updated = $this->service->unpublish($course->id);

        return $this->success(new CourseResource($updated), __('messages.courses.unpublished'));
    }

    public function generateEnrollmentKey(Course $course)
    {
        $courseWithAdmins = $this->service->findWithAdmins($course->id);
        $this->authorize('update', $courseWithAdmins);
        $result = $this->service->updateEnrollmentSettings($course->id, ['enrollment_type' => 'key_based']);

        return $this->success(
            ['course' => new CourseResource($result['course']), 'enrollment_key' => $result['enrollment_key']],
            __('messages.courses.key_generated'),
        );
    }

    public function updateEnrollmentKey(PublishCourseRequest $request, Course $course)
    {
        $courseWithAdmins = $this->service->findWithAdmins($course->id);
        $this->authorize('update', $courseWithAdmins);
        $result = $this->service->updateEnrollmentSettings($course->id, $request->validated());

        return $this->success(
            ['course' => new CourseResource($result['course']), 'enrollment_key' => $result['enrollment_key']],
            __('messages.courses.enrollment_settings_updated')
        );
    }

    public function removeEnrollmentKey(Course $course)
    {
        $courseWithAdmins = $this->service->findWithAdmins($course->id);
        $this->authorize('update', $courseWithAdmins);
        $result = $this->service->updateEnrollmentSettings($course->id, ['enrollment_type' => 'auto_accept']);

        return $this->success(new CourseResource($result['course']), __('messages.courses.key_removed'));
    }

    public function myEnrolledCourses(Request $request)
    {
        $userId = auth('api')->id();
        $perPage = (int) $request->query('per_page', 15);

        $paginator = $this->service->listEnrolledCourses($userId, $request->all(), $perPage);
        $paginator->getCollection()->transform(fn ($course) => new \Modules\Schemes\Http\Resources\CourseIndexResource($course));

        return $this->paginateResponse($paginator, 'messages.courses.list_retrieved');
    }
}
