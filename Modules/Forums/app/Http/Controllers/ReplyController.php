<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Forums\Contracts\Repositories\ReplyRepositoryInterface;
use Modules\Forums\Http\Requests\CreateReplyRequest;
use Modules\Forums\Http\Requests\UpdateReplyRequest;
use Modules\Forums\Http\Resources\ReplyResource;
use Modules\Forums\Models\Reply;
use Modules\Forums\Models\Thread;
use Modules\Forums\Services\ModerationService;
use Modules\Forums\Services\ReplyService;
use Modules\Schemes\Models\Course;

class ReplyController extends Controller
{
    use ApiResponse;

    public function index(Request $request, Course $course, Thread $thread, ReplyRepositoryInterface $replyRepository): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);
        $page = (int) $request->input('page', 1);
        $replies = $replyRepository->paginateTopLevelReplies($thread->id, $perPage, $page);
        $replies->getCollection()->transform(fn ($item) => new ReplyResource($item));
        return $this->paginateResponse($replies, __('messages.forums.replies_retrieved'));
    }

    public function store(CreateReplyRequest $request, Course $course, Thread $thread, ReplyService $replyService, ReplyRepositoryInterface $replyRepository): JsonResponse
    {
        $this->authorize('create', [Reply::class, $thread]);
        $reply = $replyService->create($request->validated(), $request->user(), $thread, null, $request->file('attachments') ?? []);
        $replyWithIncludes = $replyRepository->findWithRelations($reply->id);
        return $this->created(new ReplyResource($replyWithIncludes), __('messages.forums.reply_created'));
    }

    public function update(UpdateReplyRequest $request, Course $course, Reply $reply, ReplyService $replyService, ReplyRepositoryInterface $replyRepository): JsonResponse
    {
        $this->authorize('update', $reply);
        $updatedReply = $replyService->update($reply, $request->validated());
        $replyWithIncludes = $replyRepository->findWithRelations($updatedReply->id);
        return $this->success(new ReplyResource($replyWithIncludes), __('messages.forums.reply_updated'));
    }

    public function destroy(Request $request, Course $course, Reply $reply, ReplyService $replyService): JsonResponse
    {
        $this->authorize('delete', $reply);
        $replyService->delete($reply, $request->user());
        return $this->success(null, __('messages.forums.reply_deleted'));
    }

    public function accept(Request $request, Course $course, Thread $thread, Reply $reply, ModerationService $moderationService, ReplyRepositoryInterface $replyRepository): JsonResponse
    {
        $this->authorize('markAsAccepted', $reply);
        $updatedReply = $moderationService->markAsAcceptedAnswer($reply, $request->user());
        $replyWithIncludes = $replyRepository->findWithRelations($updatedReply->id);
        return $this->success(new ReplyResource($replyWithIncludes), __('messages.forums.reply_updated'));
    }

    public function unaccept(Request $request, Course $course, Thread $thread, Reply $reply, ModerationService $moderationService, ReplyRepositoryInterface $replyRepository): JsonResponse
    {
        $this->authorize('markAsAccepted', $reply);
        $updatedReply = $moderationService->unmarkAsAcceptedAnswer($reply, $request->user());
        $replyWithIncludes = $replyRepository->findWithRelations($updatedReply->id);
        return $this->success(new ReplyResource($replyWithIncludes), __('messages.forums.reply_updated'));
    }
}
