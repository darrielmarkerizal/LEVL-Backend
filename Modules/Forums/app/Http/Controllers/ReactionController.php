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

    public function storeThreadReaction(Request $request, int $courseId, Thread $thread): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:like,helpful,solved',
        ]);

        $added = Reaction::toggle(
            $request->user()->id,
            Thread::class,
            $thread->id,
            $request->input('type')
        );

        $message = $added ? __('forums.reaction_added') : __('forums.reaction_removed');

        if ($added) {
            $reaction = $this->reactionRepository->findByUserAndReactable(
                $request->user()->id,
                Thread::class,
                $thread->id
            );

            if ($reaction) {
                event(new \Modules\Forums\Events\ReactionAdded($reaction));
            }
        }

        return $this->success(['added' => $added], $message);
    }

    public function destroyThreadReaction(Request $request, int $courseId, Thread $thread, Reaction $reaction): JsonResponse
    {
        if ($reaction->reactable_type !== Thread::class || $reaction->reactable_id !== $thread->id) {
            return $this->notFound(__('forums.reaction_not_found'));
        }

        $this->authorize('delete', $reaction);

        $reaction->delete();

        return $this->success(null, __('forums.reaction_removed'));
    }

    public function storeReplyReaction(Request $request, int $courseId, Reply $reply): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:like,helpful,solved',
        ]);

        $added = Reaction::toggle(
            $request->user()->id,
            Reply::class,
            $reply->id,
            $request->input('type')
        );

        $message = $added ? __('forums.reaction_added') : __('forums.reaction_removed');

        if ($added) {
            $reaction = $this->reactionRepository->findByUserAndReactable(
                $request->user()->id,
                Reply::class,
                $reply->id
            );

            if ($reaction) {
                event(new \Modules\Forums\Events\ReactionAdded($reaction));
            }
        }

        return $this->success(['added' => $added], $message);
    }

    public function destroyReplyReaction(Request $request, int $courseId, Reply $reply, Reaction $reaction): JsonResponse
    {
        if ($reaction->reactable_type !== Reply::class || $reaction->reactable_id !== $reply->id) {
            return $this->notFound(__('forums.reaction_not_found'));
        }

        $this->authorize('delete', $reaction);

        $reaction->delete();

        return $this->success(null, __('forums.reaction_removed'));
    }
}
