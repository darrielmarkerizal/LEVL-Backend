<?php

declare(strict_types=1);

namespace Modules\Forums\Services;

use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Forums\Contracts\Repositories\ThreadRepositoryInterface;
use Modules\Forums\Events\ThreadCreated;
use Modules\Forums\Models\Thread;

class ThreadService
{
    public function __construct(
        private readonly ThreadRepositoryInterface $repository,
    ) {}

    public function create(array $data, User $actor, int $courseId, array $files = []): Thread
    {
        $this->validateContent($data['content']);

        return DB::transaction(function () use ($data, $actor, $courseId, $files) {
            $threadData = [
                'course_id' => $courseId,
                'author_id' => $actor->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'last_activity_at' => now(),
            ];

            $thread = $this->repository->create($threadData);

            foreach ($files as $file) {
                $thread->addMedia($file)->toMediaCollection('attachments');
            }

            event(new ThreadCreated($thread));

            $this->processMentions($thread, $data['content']);

            return $thread->fresh();
        });
    }

    public function update(Thread $thread, array $data): Thread
    {
        $updateData = [];

        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }

        if (isset($data['content'])) {
            $this->validateContent($data['content']);
            $updateData['content'] = $data['content'];
            $updateData['edited_at'] = now();
        }

        if (! empty($updateData)) {
            $updatedThread = $this->repository->update($thread, $updateData);

            if (isset($data['content'])) {
                $this->processMentions($updatedThread, $data['content']);
            }

            return $updatedThread->fresh();
        }

        return $thread;
    }

    public function delete(Thread $thread, User $actor): bool
    {
        return $this->repository->delete($thread, $actor->id);
    }

    private function processMentions(Thread $thread, string $content): void
    {
        $mentionedUsers = $this->extractMentions($content);
        $thread->mentions()->delete();

        if ($mentionedUsers->isEmpty()) {
            return;
        }

        $mentions = $mentionedUsers->map(fn ($user) => [
            'user_id' => $user->id,
            'mentionable_type' => Thread::class,
            'mentionable_id' => $thread->id,
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
