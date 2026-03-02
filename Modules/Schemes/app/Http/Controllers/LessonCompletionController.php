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
    use ApiResponse;

    public function __construct(
        private readonly LessonCompletionService $service
    ) {}

    public function markComplete(Lesson $lesson): JsonResponse
    {
        $user = auth('api')->user();
        $completion = $this->service->markAsCompleted($lesson, $user->id);

        return $this->success($completion, __('messages.lesson_marked_complete'));
    }

    public function markIncomplete(Lesson $lesson): JsonResponse
    {
        $user = auth('api')->user();
        $this->service->unmarkAsCompleted($lesson, $user->id);

        return $this->success(null, __('messages.lesson_marked_incomplete'));
    }
}
