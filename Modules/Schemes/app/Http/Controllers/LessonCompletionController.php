<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Services\LessonCompletionService;

class LessonCompletionController extends Controller
{
    use ApiResponse, \Modules\Schemes\Traits\ValidatesEnrollment;

    public function __construct(
        private readonly LessonCompletionService $service
    ) {}

    public function markComplete(Lesson $lesson): JsonResponse
    {
        try {
            $user = auth('api')->user();
            $course = $lesson->unit->course;

            
            if ($error = $this->requireEnrollment($course)) {
                return $error;
            }

            $completion = $this->service->markAsCompleted($lesson, $user->id);

            return $this->success($completion, __('messages.lessons.marked_complete'));
        } catch (\Modules\Schemes\Exceptions\LessonCompletionException $e) {
            return $this->error($e->getMessage(), [], $e->getCode() ?: 422);
        }
    }

    public function markIncomplete(Lesson $lesson): JsonResponse
    {
        try {
            $user = auth('api')->user();
            $course = $lesson->unit->course;

            
            if ($error = $this->requireEnrollment($course)) {
                return $error;
            }

            $this->service->unmarkAsCompleted($lesson, $user->id);

            return $this->success(null, __('messages.lessons.marked_incomplete'));
        } catch (\Modules\Schemes\Exceptions\LessonCompletionException $e) {
            return $this->error($e->getMessage(), [], $e->getCode() ?: 422);
        }
    }
}
