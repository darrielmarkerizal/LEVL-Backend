<?php

namespace Modules\Forums\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Forums\Contracts\Repositories\ReactionRepositoryInterface;
use Modules\Forums\Models\Reaction;
use Modules\Forums\Models\Reply;
use Modules\Forums\Models\Thread;

 
class ReactionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ReactionRepositoryInterface $reactionRepository
    ) {}

     
    public function toggleThreadReaction(Request $request, int $threadId): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:like,helpful,solved',
        ]);

        $thread = Thread::find($threadId);

        if (! $thread) {
            return $this->notFound(__('forums.thread_not_found'));
        }

        $added = Reaction::toggle(
            $request->user()->id,
            Thread::class,
            $threadId,
            $request->input('type')
        );

        $message = $added ? __('forums.reaction_added') : __('forums.reaction_removed');

        if ($added) {
            $reaction = $this->reactionRepository->findByUserAndReactable(
                $request->user()->id,
                Thread::class,
                $threadId
            );

            if ($reaction) {
                event(new \Modules\Forums\Events\ReactionAdded($reaction));
            }
        }

        return $this->success(['added' => $added], $message);
    }

     
    public function toggleReplyReaction(Request $request, int $replyId): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:like,helpful,solved',
        ]);

        $reply = Reply::find($replyId);

        if (! $reply) {
            return $this->notFound(__('forums.reply_not_found'));
        }

        $added = Reaction::toggle(
            $request->user()->id,
            Reply::class,
            $replyId,
            $request->input('type')
        );

        $message = $added ? __('forums.reaction_added') : __('forums.reaction_removed');

        if ($added) {
            $reaction = $this->reactionRepository->findByUserAndReactable(
                $request->user()->id,
                Reply::class,
                $replyId
            );

            if ($reaction) {
                event(new \Modules\Forums\Events\ReactionAdded($reaction));
            }
        }

        return $this->success(['added' => $added], $message);
    }
}
