<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Schemes\Http\Requests\LessonBlockRequest;
use Modules\Schemes\Http\Resources\LessonBlockResource;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\LessonBlock;
use Modules\Schemes\Models\Unit;
use Modules\Schemes\Services\LessonBlockService;

class LessonBlockController extends Controller
{
    use ApiResponse, AuthorizesRequests, \Modules\Schemes\Traits\ValidatesEnrollment;

    public function __construct(
        private readonly LessonBlockService $service,
        private readonly \Modules\Schemes\Services\PrerequisiteService $prerequisiteService
    ) {}

    public function index(Request $request, Course $course, Unit $unit, Lesson $lesson)
    {
        $this->service->validateHierarchy($course->id, $unit->id, $lesson->id);
        $this->authorize('view', $lesson);

        // Require enrollment for students
        if ($error = $this->requireEnrollment($course)) {
            return $error;
        }

        $user = auth('api')->user();
        if ($user && $user->hasRole('Student')) {
            $accessCheck = $this->prerequisiteService->checkLessonAccess($lesson, $user->id);
            if (! $accessCheck['accessible']) {
                return $this->error(__('messages.lessons.locked_prerequisite'), [], 403);
            }
        }

        $blocks = $this->service->list($lesson->id, $request->query('filter', []));

        return $this->success(LessonBlockResource::collection($blocks));
    }

    public function store(LessonBlockRequest $request, Course $course, Unit $unit, Lesson $lesson)
    {
        $this->service->validateHierarchy($course->id, $unit->id, $lesson->id);
        $this->authorize('update', $lesson);

        $block = $this->service->create($lesson->id, $request->validated(), $request->file('media'));

        return $this->created(new LessonBlockResource($block), __('messages.lesson_blocks.created'));
    }

    public function show(Course $course, Unit $unit, Lesson $lesson, LessonBlock $block)
    {
        $this->service->validateHierarchy($course->id, $unit->id, $lesson->id);
        $this->authorize('view', $block);

        // Require enrollment for students
        if ($error = $this->requireEnrollment($course)) {
            return $error;
        }

        $user = auth('api')->user();
        if ($user && $user->hasRole('Student')) {
            $accessCheck = $this->prerequisiteService->checkLessonAccess($lesson, $user->id);
            if (! $accessCheck['accessible']) {
                return $this->error(__('messages.lessons.locked_prerequisite'), [], 403);
            }
        }

        return $this->success(new LessonBlockResource($block));
    }

    public function update(LessonBlockRequest $request, Course $course, Unit $unit, Lesson $lesson, LessonBlock $block)
    {
        $this->service->validateHierarchy($course->id, $unit->id, $lesson->id);
        $this->authorize('update', $block);

        $updated = $this->service->update($lesson->id, $block->id, $request->validated(), $request->file('media'));

        return $this->success(new LessonBlockResource($updated), __('messages.lesson_blocks.updated'));
    }

    public function reorder(Request $request, Course $course, Unit $unit, Lesson $lesson)
    {
        $this->service->validateHierarchy($course->id, $unit->id, $lesson->id);
        $this->authorize('update', $lesson);

        $request->validate([
            'blocks' => 'required|array',
            'blocks.*.id' => 'required|integer|exists:lesson_blocks,id',
            'blocks.*.order' => 'required|integer|min:1',
        ]);

        $this->service->reorder($lesson->id, $request->input('blocks'));

        return $this->success([], __('messages.lesson_blocks.reordered'));
    }

    public function destroy(Course $course, Unit $unit, Lesson $lesson, LessonBlock $block)
    {
        $this->service->validateHierarchy($course->id, $unit->id, $lesson->id);
        $this->authorize('delete', $block);

        $this->service->delete($lesson->id, $block->id);

        return $this->success([], __('messages.lesson_blocks.deleted'));
    }

    public function bulkDestroy(Request $request, Course $course, Unit $unit, Lesson $lesson)
    {
        $this->service->validateHierarchy($course->id, $unit->id, $lesson->id);
        $this->authorize('update', $lesson);

        $validated = $request->validate([
            'block_ids' => 'required|array',
            'block_ids.*' => 'required|integer',
        ]);

        // Only delete blocks that belong to this lesson
        $blockIds = $validated['block_ids'];
        $deleted = $this->service->bulkDelete($lesson->id, $blockIds);

        if ($deleted === 0) {
            return $this->error(__('messages.lesson_blocks.not_found'), [], 404);
        }

        return $this->success(
            ['deleted_count' => $deleted],
            __('messages.lesson_blocks.bulk_deleted', ['count' => $deleted])
        );
    }
}
