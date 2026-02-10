<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Forums\Http\Requests\CreateThreadRequest;
use Modules\Forums\Http\Requests\UpdateThreadRequest;
use Modules\Forums\Http\Resources\ThreadResource;
use Modules\Forums\Models\Thread;
use Modules\Forums\Services\ThreadDashboardService;
use Modules\Forums\Services\ThreadReadService;
use Modules\Forums\Services\ThreadService;
use Modules\Forums\Services\ModerationService;
use Modules\Schemes\Models\Course;

class ThreadController extends Controller
{
    use ApiResponse;

    public function index(Request $request, Course $course, ThreadReadService $threadReadService): JsonResponse
    {
        $threads = $threadReadService->paginateCourseThreads($course->id, $request->input('search'), (int) $request->input('per_page', 20));
        return $this->paginateResponse($threads, __('messages.forums.threads_retrieved'));
    }

    public function store(CreateThreadRequest $request, Course $course, ThreadService $threadService, ThreadDashboardService $dashboardService): JsonResponse
    {
        $thread = $threadService->create($request->validated(), $request->user(), $course->id, $request->file('attachments') ?? []);
        $threadWithIncludes = $dashboardService->getWithIncludes($thread);
        return $this->created(new ThreadResource($threadWithIncludes), __('messages.forums.thread_created'));
    }

    public function show(Request $request, Course $course, Thread $thread, ThreadReadService $threadReadService): JsonResponse
    {
        $threadDetail = $threadReadService->getThreadDetail($thread->id);
        return $this->success(new ThreadResource($threadDetail), __('messages.forums.thread_retrieved'));
    }

    public function update(UpdateThreadRequest $request, Course $course, Thread $thread, ThreadService $threadService, ThreadDashboardService $dashboardService): JsonResponse
    {
        $this->authorize('update', $thread);
        $updatedThread = $threadService->update($thread, $request->validated());
        $threadWithIncludes = $dashboardService->getWithIncludes($updatedThread);
        return $this->success(new ThreadResource($threadWithIncludes), __('messages.forums.thread_updated'));
    }

    public function destroy(Request $request, Course $course, Thread $thread, ThreadService $threadService): JsonResponse
    {
        $this->authorize('delete', $thread);
        $threadService->delete($thread, $request->user());
        return $this->success(null, __('messages.forums.thread_deleted'));
    }

    public function pin(Request $request, Course $course, Thread $thread, ModerationService $moderationService, ThreadDashboardService $dashboardService): JsonResponse
    {
        $this->authorize('pin', $thread);
        $pinnedThread = $moderationService->pinThread($thread, $request->user());
        $threadWithIncludes = $dashboardService->getWithIncludes($pinnedThread);
        return $this->success(new ThreadResource($threadWithIncludes), __('messages.forums.thread_pinned'));
    }

    public function unpin(Request $request, Course $course, Thread $thread, ModerationService $moderationService, ThreadDashboardService $dashboardService): JsonResponse
    {
        $this->authorize('unpin', $thread);
        $unpinnedThread = $moderationService->unpinThread($thread, $request->user());
        $threadWithIncludes = $dashboardService->getWithIncludes($unpinnedThread);
        return $this->success(new ThreadResource($threadWithIncludes), __('messages.forums.thread_unpinned'));
    }

    public function close(Request $request, Course $course, Thread $thread, ModerationService $moderationService, ThreadDashboardService $dashboardService): JsonResponse
    {
        $this->authorize('close', $thread);
        $closedThread = $moderationService->closeThread($thread, $request->user());
        $threadWithIncludes = $dashboardService->getWithIncludes($closedThread);
        return $this->success(new ThreadResource($threadWithIncludes), __('messages.forums.thread_closed'));
    }

    public function open(Request $request, Course $course, Thread $thread, ModerationService $moderationService, ThreadDashboardService $dashboardService): JsonResponse
    {
        $this->authorize('open', $thread);
        $openedThread = $moderationService->openThread($thread, $request->user());
        $threadWithIncludes = $dashboardService->getWithIncludes($openedThread);
        return $this->success(new ThreadResource($threadWithIncludes), __('messages.forums.thread_opened'));
    }

    public function resolve(Request $request, Course $course, Thread $thread, ModerationService $moderationService, ThreadDashboardService $dashboardService): JsonResponse
    {
        $this->authorize('resolve', $thread);
        $resolvedThread = $moderationService->resolveThread($thread, $request->user());
        $threadWithIncludes = $dashboardService->getWithIncludes($resolvedThread);
        return $this->success(new ThreadResource($threadWithIncludes), __('messages.forums.thread_resolved'));
    }

    public function unresolve(Request $request, Course $course, Thread $thread, ModerationService $moderationService, ThreadDashboardService $dashboardService): JsonResponse
    {
        $this->authorize('unresolve', $thread);
        $unresolvedThread = $moderationService->unresolveThread($thread, $request->user());
        $threadWithIncludes = $dashboardService->getWithIncludes($unresolvedThread);
        return $this->success(new ThreadResource($threadWithIncludes), __('messages.forums.thread_unresolved'));
    }
}
