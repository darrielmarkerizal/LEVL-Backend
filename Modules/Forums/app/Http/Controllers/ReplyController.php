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

/**
 * @tags Forum Diskusi
 */
class ReplyController extends Controller
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
     * @summary Buat Balasan Baru
     */
    public function store(CreateReplyRequest $request, int $threadId): JsonResponse
    {
        $thread = Thread::find($threadId);

        if (! $thread) {
            return $this->notFound(__('forums.thread_not_found'));
        }

        $this->authorize('create', [Reply::class, $thread]);

        try {
            $parentId = $request->input('parent_id');
            if ($parentId) {
                $parent = Reply::find($parentId);
                if (! $parent || $parent->thread_id != $threadId) {
                    return $this->error(__('forums.invalid_parent_reply'), 400);
                }
            }

            $reply = $this->forumService->createReply(
                $thread,
                $request->validated(),
                $request->user(),
                $parentId
            );

            return $this->created($reply, __('forums.reply_created'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * @summary Perbarui Balasan
     */
    public function update(UpdateReplyRequest $request, int $replyId): JsonResponse
    {
        $reply = Reply::find($replyId);

        if (! $reply) {
            return $this->notFound(__('forums.reply_not_found'));
        }

        $this->authorize('update', $reply);

        try {
            $updatedReply = $this->forumService->updateReply($reply, $request->validated());

            return $this->success($updatedReply, __('forums.reply_updated'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * @summary Hapus Balasan
     */
    public function destroy(Request $request, int $replyId): JsonResponse
    {
        $reply = Reply::find($replyId);

        if (! $reply) {
            return $this->notFound(__('forums.reply_not_found'));
        }

        $this->authorize('delete', $reply);

        try {
            $this->forumService->deleteReply($reply, $request->user());

            return $this->success(null, __('forums.reply_deleted'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * @summary Terima Balasan sebagai Jawaban
     */
    public function accept(Request $request, int $replyId): JsonResponse
    {
        $reply = Reply::find($replyId);

        if (! $reply) {
            return $this->notFound(__('forums.reply_not_found'));
        }

        $this->authorize('markAsAccepted', $reply);

        try {
            $acceptedReply = $this->moderationService->markAsAcceptedAnswer($reply, $request->user());

            return $this->success($acceptedReply, __('forums.reply_accepted'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
