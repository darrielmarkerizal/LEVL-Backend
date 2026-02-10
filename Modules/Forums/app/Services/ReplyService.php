<?php

declare(strict_types=1);

namespace Modules\Forums\Services;

use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Forums\Contracts\Repositories\ReplyRepositoryInterface;
use Modules\Forums\Events\ReplyCreated;
use Modules\Forums\Models\Reply;
use Modules\Forums\Models\Thread;

class ReplyService
{
    public function __construct(
        private readonly ReplyRepositoryInterface $repository,
    ) {}

    public function create(array $data, User $actor, Thread $thread, ?int $parentId = null, array $files = []): Reply
    {
        $this->validateReplyForThread($thread, $parentId);
        $this->validateContent($data['content']);

        return DB::transaction(function () use ($data, $actor, $thread, $parentId, $files) {
            $reply = $this->repository->create([
                'thread_id' => $thread->id,
                'parent_id' => $parentId,
                'author_id' => $actor->id,
                'content' => $data['content'],
            ]);

            foreach ($files as $file) {
                $reply->addMedia($file)->toMediaCollection('attachments');
            }

            $thread->increment('replies_count');
            $thread->updateLastActivity();

            event(new ReplyCreated($reply));

            $this->processMentions($reply, $data['content']);

            return $reply->fresh();
        });
    }

    public function update(Reply $reply, array $data): Reply
    {
        $updateData = [];

        if (isset($data['content'])) {
            $this->validateContent($data['content']);
            $updateData['content'] = $data['content'];
            $updateData['edited_at'] = now();
        }

        if (! empty($updateData)) {
            $updatedReply = $this->repository->update($reply, $updateData);

            if (isset($data['content'])) {
                $this->processMentions($updatedReply, $data['content']);
            }

            return $updatedReply->fresh();
        }

        return $reply;
    }

    public function delete(Reply $reply, User $actor): bool
    {
        return DB::transaction(function () use ($reply, $actor) {
            $thread = $reply->thread;
            $result = $this->repository->delete($reply, $actor->id);

            if ($result) {
                $thread->decrement('replies_count');
            }

            return $result;
        });
    }

    private function validateReplyForThread(Thread $thread, ?int $parentId): void
    {
        if ($thread->isClosed()) {
            throw new \Exception(__('messages.forums.cannot_reply_closed_thread'));
        }

        if ($parentId) {
            $parent = Reply::find($parentId);
            if ($parent && ! $parent->canHaveChildren()) {
                throw new \Exception(__('messages.forums.max_reply_depth_exceeded'));
            }
        }
    }

    private function processMentions(Reply $reply, string $content): void
    {
        $mentionedUsers = $this->extractMentions($content);
        $reply->mentions()->delete();

        if ($mentionedUsers->isEmpty()) {
            return;
        }

        $mentions = $mentionedUsers->map(fn ($user) => [
            'user_id' => $user->id,
            'mentionable_type' => Reply::class,
            'mentionable_id' => $reply->id,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        \Modules\Forums\Models\Mention::insert($mentions);
    }

    private function extractMentions(string $content): \Illuminate\Support\Collection
    {
        preg_match_all('/@([a-zA-Z0-9_]+)/', $content, $matches);

        if (empty($matches[1])) {
            return collect();
        }

        return User::whereIn('username', $matches[1])->get();
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
