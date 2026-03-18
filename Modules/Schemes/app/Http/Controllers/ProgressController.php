<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Schemes\Http\Resources\ProgressResource;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;
use Modules\Schemes\Services\ProgressionService;

class ProgressController extends Controller
{
    use ApiResponse, AuthorizesRequests, \Modules\Schemes\Traits\ValidatesEnrollment;

    public function __construct(private readonly ProgressionService $progression) {}

    public function show(Request $request, Course $course)
    {
        // Require enrollment for students
        if ($error = $this->requireEnrollment($course)) {
            return $error;
        }

        $targetId = (int) ($request->query('user_id') ?? auth('api')->id());

        if ($targetId !== auth('api')->id()) {
            $this->authorize('viewAny', [\Modules\Enrollments\Models\Enrollment::class, $course]);
        }

        $result = $this->progression->validateAndGetProgress($course, $targetId, auth('api')->id());

        return $this->success(new ProgressResource($result));
    }

    public function completeLesson(Request $request, Course $course, Unit $unit, Lesson $lesson)
    {
        // Require enrollment for students
        if ($error = $this->requireEnrollment($course)) {
            return $error;
        }

        $enrollment = $this->progression->validateLessonAccess($course, $unit, $lesson, (int) auth('api')->id());
        $this->progression->markLessonCompleted($lesson, $enrollment);

        return $this->success(
            new ProgressResource($this->progression->getCourseProgressData($course, $enrollment)),
            __('messages.progress.updated')
        );
    }

    public function uncompleteLesson(Request $request, Course $course, Unit $unit, Lesson $lesson)
    {
        // Require enrollment for students
        if ($error = $this->requireEnrollment($course)) {
            return $error;
        }

        $enrollment = $this->progression->validateLessonAccess($course, $unit, $lesson, (int) auth('api')->id());
        $this->progression->markLessonUncompleted($lesson, $enrollment);

        return $this->success(
            new ProgressResource($this->progression->getCourseProgressData($course, $enrollment)),
            __('messages.progress.updated')
        );
    }
}
