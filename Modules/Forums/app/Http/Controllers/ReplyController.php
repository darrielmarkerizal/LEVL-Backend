<?php

namespace Modules\Forums\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Forums\Contracts\Services\ForumServiceInterface;
use Modules\Forums\Contracts\Services\ModerationServiceInterface;
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

     
    public function store(CreateReplyRequest $request, int $threadId): JsonResponse
    {
        $thread = Thread::find($threadId);

        if (! $thread) {
            return $this->notFound(__('forums.thread_not_found'));
        }

        $this->authorize('create', [Reply::class, $thread]);

        $parentId = $request->input('parent_id');
        
        

        try {
            $reply = $this->forumService->createReply(
                $thread,
                $request->validated(),
                $request->user(),
                $parentId
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }

        return $this->created($reply, __('forums.reply_created'));
    }

     
    public function update(UpdateReplyRequest $request, Reply $reply): JsonResponse
    {
        $this->authorize('update', $reply);

        $updatedReply = $this->forumService->updateReply($reply, $request->validated());

        return $this->success($updatedReply, __('forums.reply_updated'));
    }

     
    public function destroy(Request $request, Reply $reply): JsonResponse
    {
        $this->authorize('delete', $reply);

        $this->forumService->deleteReply($reply, $request->user());

        return $this->success(null, __('forums.reply_deleted'));
    }

     
    public function accept(Request $request, Reply $reply): JsonResponse
    {
        $this->authorize('markAsAccepted', $reply);

        $acceptedReply = $this->moderationService->markAsAcceptedAnswer($reply, $request->user());

        return $this->success($acceptedReply, __('forums.reply_accepted'));
    }
}
