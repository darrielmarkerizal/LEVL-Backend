<?php

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

/**
 * @tags Forum Diskusi
 */
class ThreadController extends Controller
{
    use ApiResponse;

    protected ForumServiceInterface $forumService;

    protected ModerationService $moderationService;

    public function __construct(
        ForumServiceInterface $forumService,
        ModerationService $moderationService
    ) {
        $this->forumService = $forumService;
        $this->moderationService = $moderationService;
    }

    /**
     * @summary Daftar Thread Forum
     */
    public function index(Request $request, int $schemeId): JsonResponse
    {
        $filters = [
            'pinned' => $request->boolean('pinned'),
            'resolved' => $request->boolean('resolved'),
            'closed' => $request->has('closed') ? $request->boolean('closed') : null,
            'per_page' => $request->input('per_page', 20),
        ];

        $threads = $this->forumService->getThreadsForScheme($schemeId, $filters);

        return $this->paginateResponse($threads, __('forums.threads_retrieved'));
    }

    /**
     * @summary Buat Thread Baru
     */
    public function store(CreateThreadRequest $request, int $schemeId): JsonResponse
    {
        try {
            $data = array_merge($request->validated(), ['scheme_id' => $schemeId]);
            $thread = $this->forumService->createThread($data, $request->user());

            return $this->created($thread, __('forums.thread_created'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * @summary Detail Thread
     */
    public function show(int $schemeId, int $threadId): JsonResponse
    {
        $thread = $this->forumService->getThreadDetail($threadId);

        if (! $thread || $thread->scheme_id != $schemeId) {
            return $this->notFound(__('forums.thread_not_found'));
        }

        return $this->success($thread, __('forums.thread_retrieved'));
    }

    /**
     * @summary Perbarui Thread
     */
    public function update(UpdateThreadRequest $request, int $schemeId, int $threadId): JsonResponse
    {
        $thread = Thread::find($threadId);

        if (! $thread || $thread->scheme_id != $schemeId) {
            return $this->notFound(__('forums.thread_not_found'));
        }

        $this->authorize('update', $thread);

        try {
            $updatedThread = $this->forumService->updateThread($thread, $request->validated());

            return $this->success($updatedThread, __('forums.thread_updated'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * @summary Hapus Thread
     */
    public function destroy(Request $request, int $schemeId, int $threadId): JsonResponse
    {
        $thread = Thread::find($threadId);

        if (! $thread || $thread->scheme_id != $schemeId) {
            return $this->notFound(__('forums.thread_not_found'));
        }

        $this->authorize('delete', $thread);

        try {
            $this->forumService->deleteThread($thread, $request->user());

            return $this->success(null, __('forums.thread_deleted'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * @summary Sematkan Thread
     */
    public function pin(Request $request, int $schemeId, int $threadId): JsonResponse
    {
        $thread = Thread::find($threadId);

        if (! $thread || $thread->scheme_id != $schemeId) {
            return $this->notFound(__('forums.thread_not_found'));
        }

        $this->authorize('pin', $thread);

        try {
            $pinnedThread = $this->moderationService->pinThread($thread, $request->user());

            return $this->success($pinnedThread, __('forums.thread_pinned'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * @summary Tutup Thread
     */
    public function close(Request $request, int $schemeId, int $threadId): JsonResponse
    {
        $thread = Thread::find($threadId);

        if (! $thread || $thread->scheme_id != $schemeId) {
            return $this->notFound(__('forums.thread_not_found'));
        }

        $this->authorize('close', $thread);

        try {
            $closedThread = $this->moderationService->closeThread($thread, $request->user());

            return $this->success($closedThread, __('forums.thread_closed'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * @summary Cari Thread
     */
    public function search(Request $request, int $schemeId): JsonResponse
    {
        $query = $request->input('q', '');

        if (empty($query)) {
            return $this->error(__('forums.search_query_required'), 400);
        }

        $threads = $this->forumService->searchThreads($query, $schemeId);

        return $this->success($threads, __('forums.search_results_retrieved'));
    }
}
