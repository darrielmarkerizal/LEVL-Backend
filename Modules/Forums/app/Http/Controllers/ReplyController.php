<?php

namespace Modules\Forums\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Forums\Contracts\Services\ForumServiceInterface;
use Modules\Forums\Http\Requests\CreateReplyRequest;
use Modules\Forums\Http\Requests\UpdateReplyRequest;
use Modules\Forums\Http\Resources\ReplyResource;
use Modules\Forums\Models\Reply;
use Modules\Forums\Models\Thread;
use Modules\Forums\Services\ModerationService;
use Modules\Schemes\Models\Course;

class ReplyController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ForumServiceInterface $forumService,
        private readonly ModerationService $moderationService
    ) {}

    public function index(Course $course, int $threadId): JsonResponse
    {
        $thread = Thread::find($threadId);

        if (! $thread) {
            return $this->notFound(__('messages.forums.thread_not_found'));
        }

        $replies = $thread->replies()
            ->topLevel()
            ->with([
                'author',
                'media',
                'children' => function ($query) {
                    $query->with(['author', 'media', 'children' => function ($query) {
                        $query->with(['author', 'media', 'children']);
                    }]);
                }
            ])
            ->paginate(20);

        return $this->paginateResponse(ReplyResource::collection($replies), __('messages.forums.replies_retrieved'));
    }

    public function store(CreateReplyRequest $request, Course $course, int $threadId): JsonResponse
    {
        $thread = Thread::find($threadId);

        if (! $thread) {
            return $this->notFound(__('messages.forums.thread_not_found'));
        }

        $this->authorize('create', [Reply::class, $thread]);

        try {
            $data = $request->validated();
            $data['attachments'] = $request->file('attachments') ?? [];

            $reply = $this->forumService->createReply(
                $thread,
                $data,
                $request->user()
            );

            $reply->load('author', 'media');

            return $this->created(new ReplyResource($reply), __('messages.forums.reply_created'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), [], 400);
        }
    }

    public function update(UpdateReplyRequest $request, Course $course, Reply $reply): JsonResponse
    {
        $this->authorize('update', $reply);

        $updatedReply = $this->forumService->updateReply($reply, $request->validated());

        $updatedReply->load('author', 'media');

        return $this->success(new ReplyResource($updatedReply), __('messages.forums.reply_updated'));
    }

    public function destroy(Request $request, Course $course, Reply $reply): JsonResponse
    {
        $this->authorize('delete', $reply);

        $this->forumService->deleteReply($reply, $request->user());

        return $this->success(null, __('messages.forums.reply_deleted'));
    }
}
