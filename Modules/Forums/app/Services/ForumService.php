<?php

declare(strict_types=1);

namespace Modules\Forums\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Forums\Contracts\Services\ForumServiceInterface as ModuleForumServiceInterface;
use Modules\Forums\Models\Reply;
use Modules\Forums\Models\Thread;
use Modules\Forums\Repositories\ReplyRepository;
use Modules\Forums\Repositories\ThreadRepository;

class ForumService implements ModuleForumServiceInterface, \App\Contracts\Services\ForumServiceInterface
{
    public function __construct(
        private readonly ThreadRepository $threadRepository,
        private readonly ReplyRepository $replyRepository,
    ) {}

    public function createThread(array $data, User $user): Thread
    {
        $this->validateContent($data['content']);

        return DB::transaction(function () use ($data, $user) {
            $threadData = [
                'forumable_type' => $data['forumable_type'],
                'forumable_id' => $data['forumable_id'],
                'author_id' => $user->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'last_activity_at' => now(),
            ];

            $thread = $this->threadRepository->create($threadData);

            event(new \Modules\Forums\Events\ThreadCreated($thread));

            return $thread;
        });
    }

    public function updateThread(Thread $thread, array $data): Thread
    {
        $updateData = [];

        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }

        if (isset($data['content'])) {
            $this->validateContent($data['content']);
            $updateData['content'] = $data['content'];
        }

        if (!empty($updateData)) {
            $updateData['edited_at'] = now();
        }

        return $this->threadRepository->update($thread, $updateData);
    }

    public function deleteThread(Thread $thread, User $user): bool
    {
        return $this->threadRepository->delete($thread, $user->id);
    }

    public function getThreadsForumable(string $forumableType, int $forumableId, array $filters = [], ?string $search = null): LengthAwarePaginator
    {
        if ($search) {
            return $this->threadRepository->searchThreadsByForumable($search, $forumableType, $forumableId, $filters);
        }

        return $this->threadRepository->getThreadsByForumable($forumableType, $forumableId, $filters);
    }

    public function getThreadsForScheme(int $schemeId, array $filters = [], ?string $search = null): LengthAwarePaginator
    {
        return $this->getThreadsForumable(\Modules\Schemes\Models\Course::class, $schemeId, $filters, $search);
    }

    public function getThreadDetail(int $threadId): ?Thread
    {
        $thread = $this->threadRepository->findWithRelations($threadId);

        if ($thread) {
            $thread->incrementViews();
        }

        return $thread;
    }

    public function createReply(Thread $thread, array $data, User $user, ?int $parentId = null): Reply
    {
        $this->validateReplyForThread($thread, $parentId);
        $this->validateContent($data['content']);

        return DB::transaction(fn () => $this->persistReply($thread, $data, $user, $parentId));
    }

    private function validateReplyForThread(Thread $thread, ?int $parentId): void
    {
        if ($thread->isClosed()) {
            throw new \Exception(__('messages.forums.cannot_reply_closed_thread'));
        }

        if ($parentId) {
            $parent = Reply::find($parentId);
            if ($parent && !$parent->canHaveChildren()) {
                throw new \Exception(__('messages.forums.max_reply_depth_exceeded'));
            }
        }
    }

    private function persistReply(Thread $thread, array $data, User $user, ?int $parentId): Reply
    {
        $reply = $this->replyRepository->create([
            'thread_id' => $thread->id,
            'parent_id' => $parentId,
            'author_id' => $user->id,
            'content' => $data['content'],
        ]);

        $thread->increment('replies_count');
        $thread->updateLastActivity();

        event(new \Modules\Forums\Events\ReplyCreated($reply));

        return $reply;
    }

    public function updateReply(Reply $reply, array $data): Reply
    {
        $updateData = [];

        if (isset($data['content'])) {
            $this->validateContent($data['content']);
            $updateData['content'] = $data['content'];
            $updateData['edited_at'] = now();
        }

        return $this->replyRepository->update($reply, $updateData);
    }

    public function deleteReply(Reply $reply, User $user): bool
    {
        return DB::transaction(function () use ($reply, $user) {
            $thread = $reply->thread;

            $result = $this->replyRepository->delete($reply, $user->id);

            if ($result) {
                $thread->decrement('replies_count');
            }

            return $result;
        });
    }

    private function validateContent(string $content): void
    {
        if (strlen($content) < 1 || strlen($content) > 5000) {
            throw new \Exception(__('validation.invalid_content_length'));
        }

        if (preg_match('/<script|javascript:|onerror|onclick/i', $content)) {
            throw new \Exception(__('validation.invalid_content_detected'));
        }
    }
}
