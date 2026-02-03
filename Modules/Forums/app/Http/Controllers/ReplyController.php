<?php

namespace Modules\Forums\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Forums\Contracts\Services\ForumServiceInterface;
use Modules\Forums\Http\Requests\CreateReplyRequest;
use Modules\Forums\Http\Requests\UpdateReplyRequest;
use Modules\Forums\Models\Reply;
use Modules\Forums\Models\Thread;
use Modules\Forums\Services\ModerationService;

class ReplyController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ForumServiceInterface $forumService,
        private readonly ModerationService $moderationService
    ) {}

    public function index(int $courseId, int $threadId): JsonResponse
    {
        $thread = Thread::find($threadId);

        if (! $thread) {
            return $this->notFound(__('forums.thread_not_found'));
        }

        $replies = $thread->replies()->with('author')->paginate(20);

        return $this->paginateResponse($replies, __('forums.replies_retrieved'));
    }

    public function store(CreateReplyRequest $request, int $courseId, int $threadId): JsonResponse
    {
        $thread = Thread::find($threadId);

        if (! $thread) {
            return $this->notFound(__('forums.thread_not_found'));
        }

        $this->authorize('create', [Reply::class, $thread]);

        try {
            $reply = $this->forumService->createReply(
                $thread,
                $request->validated(),
                $request->user()
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }

        return $this->created($reply, __('forums.reply_created'));
    }

    public function update(UpdateReplyRequest $request, int $courseId, Reply $reply): JsonResponse
    {
        $this->authorize('update', $reply);

        $updatedReply = $this->forumService->updateReply($reply, $request->validated());

        return $this->success($updatedReply, __('forums.reply_updated'));
    }

    public function destroy(Request $request, int $courseId, Reply $reply): JsonResponse
    {
        $this->authorize('delete', $reply);

        $this->forumService->deleteReply($reply, $request->user());

        return $this->success(null, __('forums.reply_deleted'));
    }
}
