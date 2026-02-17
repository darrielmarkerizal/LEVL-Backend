<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Forums\Http\Requests\ToggleReactionRequest;
use Modules\Forums\Models\Reaction;
use Modules\Forums\Models\Reply;
use Modules\Forums\Models\Thread;
use Modules\Forums\Services\ReactionService;
use Modules\Schemes\Models\Course;

class ReactionController extends Controller
{
    use ApiResponse;

    public function storeThreadReaction(ToggleReactionRequest $request, Course $course, Thread $thread, ReactionService $reactionService): JsonResponse
    {
        $reaction = $reactionService->addThread($request->user(), $thread, $request->input('type'));
        return $this->success(['id' => $reaction->id, 'type' => $reaction->type], __('messages.forums.reaction_added'));
    }

    public function destroyThreadReaction(Request $request, Course $course, Thread $thread, Reaction $reaction, ReactionService $reactionService): JsonResponse
    {
        if ($reaction->reactable_type !== Thread::class || $reaction->reactable_id !== $thread->id) {
            return $this->notFound(__('messages.forums.reaction_not_found'));
        }
        $this->authorize('delete', $reaction);
        $reactionService->delete($reaction);
        return $this->success(null, __('messages.forums.reaction_removed'));
    }

    public function storeReplyReaction(ToggleReactionRequest $request, Course $course, string $threadId, string $replyId, ReactionService $reactionService): JsonResponse
    {
        $reply = Reply::findOrFail($replyId);
        $reaction = $reactionService->addReply($request->user(), $reply, $request->input('type'));
        return $this->success(['id' => $reaction->id, 'type' => $reaction->type], __('messages.forums.reaction_added'));
    }

    public function destroyReplyReaction(Request $request, Course $course, string $threadId, string $replyId, string $reactionId, ReactionService $reactionService): JsonResponse
    {
        $reply = Reply::findOrFail($replyId);
        $reaction = Reaction::findOrFail($reactionId);
        
        if ($reaction->reactable_type !== Reply::class || $reaction->reactable_id !== $reply->id) {
            return $this->notFound(__('messages.forums.reaction_not_found'));
        }
        $this->authorize('delete', $reaction);
        $reactionService->delete($reaction);
        return $this->success(null, __('messages.forums.reaction_removed'));
    }
}
