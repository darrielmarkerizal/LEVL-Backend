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
use Modules\Forums\Http\Resources\ThreadResource;
use Modules\Forums\Models\Thread;
use Modules\Forums\Services\ModerationService;
use Modules\Schemes\Models\Course;

class ThreadController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ForumServiceInterface $forumService,
        private readonly ModerationService $moderationService
    ) {}

    public function index(Request $request, Course $course): JsonResponse
    {
        $forumableType = $request->input('forumable_type', \Modules\Schemes\Models\Course::class);
        $forumableSlug = (string) $request->input('forumable_slug', $course->slug);
        $forumableId = $this->forumService->resolveForumableId($forumableType, $forumableSlug);
        $search = $request->input('search');
        $filters = array_merge(
            $request->only(['page', 'per_page', 'sort']),
            $request->input('filter', [])
        );

        if (! $forumableId) {
            return $this->notFound(__('messages.forums.thread_not_found'));
        }

        $threads = $this->forumService->getThreadsForumable($forumableType, $forumableId, $filters, $search);

        return $this->paginateResponse(ThreadResource::collection($threads), __('messages.forums.threads_retrieved'));
    }

    public function store(CreateThreadRequest $request, Course $course): JsonResponse
    {
        $data = $request->validated();
        $data['attachments'] = $request->file('attachments') ?? [];

        $thread = $this->forumService->createThread($data, $request->user());
        $thread->load('author', 'forumable', 'media');

        return $this->created(new ThreadResource($thread), __('messages.forums.thread_created'));
    }

    public function show(Course $course, Thread $thread): JsonResponse
    {
        $thread = $this->forumService->getThreadDetail($thread->id);
        $thread->load('author', 'forumable', 'replies.author', 'media');

        return $this->success(new ThreadResource($thread), __('messages.forums.thread_retrieved'));
    }

    public function update(UpdateThreadRequest $request, Course $course, Thread $thread): JsonResponse
    {
        $this->authorize('update', $thread);

        $updatedThread = $this->forumService->updateThread($thread, $request->validated());
        $updatedThread->load('author', 'forumable');

        return $this->success(new ThreadResource($updatedThread), __('messages.forums.thread_updated'));
    }

    public function destroy(Request $request, Course $course, Thread $thread): JsonResponse
    {
        $this->authorize('delete', $thread);

        $this->forumService->deleteThread($thread, $request->user());

        return $this->success(null, __('messages.forums.thread_deleted'));
    }

    public function pin(Request $request, Course $course, Thread $thread): JsonResponse
    {
        $this->authorize('pin', $thread);

        $pinnedThread = $this->moderationService->pinThread($thread, $request->user());
        $pinnedThread->load('author', 'forumable');

        return $this->success(new ThreadResource($pinnedThread), __('messages.forums.thread_pinned'));
    }

    public function unpin(Request $request, Course $course, Thread $thread): JsonResponse
    {
        $this->authorize('unpin', $thread);

        $unpinnedThread = $this->moderationService->unpinThread($thread, $request->user());
        $unpinnedThread->load('author', 'forumable');

        return $this->success(new ThreadResource($unpinnedThread), __('messages.forums.thread_unpinned'));
    }

    public function close(Request $request, Course $course, Thread $thread): JsonResponse
    {
        $this->authorize('close', $thread);

        $closedThread = $this->moderationService->closeThread($thread, $request->user());
        $closedThread->load('author', 'forumable');

        return $this->success(new ThreadResource($closedThread), __('messages.forums.thread_closed'));
    }
}
