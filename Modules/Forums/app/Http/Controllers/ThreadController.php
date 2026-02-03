<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Forums\Contracts\Services\ForumServiceInterface;
use Modules\Forums\Http\Requests\CreateThreadRequest;
use Modules\Forums\Http\Requests\UpdateThreadRequest;
use Modules\Forums\Models\Thread;
use Modules\Forums\Services\ModerationService;

class ThreadController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ForumServiceInterface $forumService,
        private readonly ModerationService $moderationService
    ) {}

    public function index(Request $request, int $courseId): JsonResponse
    {
        $forumableType = $request->input('forumable_type', \Modules\Schemes\Models\Course::class);
        $forumableId = (int) $request->input('forumable_id', $courseId);
        $filters = [
            'per_page' => (int) $request->input('per_page', 20),
        ];
        $search = $request->input('search');

        $threads = $this->forumService->getThreadsForumable($forumableType, $forumableId, $filters, $search);

        return $this->paginateResponse($threads, __('forums.threads_retrieved'));
    }

    public function store(CreateThreadRequest $request, int $courseId): JsonResponse
    {
        $data = $request->validated();

        $thread = $this->forumService->createThread($data, $request->user());

        return $this->created($thread, __('forums.thread_created'));
    }

    public function show(int $courseId, Thread $thread): JsonResponse
    {
        $thread = $this->forumService->getThreadDetail($thread->id);

        return $this->success($thread, __('forums.thread_retrieved'));
    }

    public function update(UpdateThreadRequest $request, int $courseId, Thread $thread): JsonResponse
    {
        $this->authorize('update', $thread);

        $updatedThread = $this->forumService->updateThread($thread, $request->validated());

        return $this->success($updatedThread, __('forums.thread_updated'));
    }

    public function destroy(Request $request, int $courseId, Thread $thread): JsonResponse
    {
        $this->authorize('delete', $thread);

        $this->forumService->deleteThread($thread, $request->user());

        return $this->success(null, __('forums.thread_deleted'));
    }

    public function pin(Request $request, int $courseId, Thread $thread): JsonResponse
    {
        $this->authorize('pin', $thread);

        $pinnedThread = $this->moderationService->pinThread($thread, $request->user());

        return $this->success($pinnedThread, __('forums.thread_pinned'));
    }

    public function unpin(Request $request, int $courseId, Thread $thread): JsonResponse
    {
        $this->authorize('unpin', $thread);

        $unpinnedThread = $this->moderationService->unpinThread($thread, $request->user());

        return $this->success($unpinnedThread, __('forums.thread_unpinned'));
    }

    public function close(Request $request, int $courseId, Thread $thread): JsonResponse
    {
        $this->authorize('close', $thread);

        $closedThread = $this->moderationService->closeThread($thread, $request->user());

        return $this->success($closedThread, __('forums.thread_closed'));
    }
}
