<?php

declare(strict_types=1);

namespace Modules\Forums\Services;

use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Forums\Contracts\Repositories\ReactionRepositoryInterface;
use Modules\Forums\Events\ReactionAdded;
use Modules\Forums\Models\Reaction;
use Modules\Forums\Models\Reply;
use Modules\Forums\Models\Thread;

class ReactionService
{
    public function __construct(
        private readonly ReactionRepositoryInterface $repository,
    ) {}

    public function toggleThread(User $actor, Thread $thread, string $type): array
    {
        return DB::transaction(function () use ($actor, $thread, $type) {
            $added = Reaction::toggle(
                $actor->id,
                Thread::class,
                $thread->id,
                $type
            );

            $reaction = null;

            if ($added) {
                $reaction = $this->repository->findByUserAndReactable(
                    $actor->id,
                    Thread::class,
                    $thread->id
                );

                if ($reaction) {
                    event(new ReactionAdded($reaction));
                }
            }

            return ['added' => $added, 'reaction' => $reaction];
        });
    }

    public function toggleReply(User $actor, Reply $reply, string $type): array
    {
        return DB::transaction(function () use ($actor, $reply, $type) {
            $added = Reaction::toggle(
                $actor->id,
                Reply::class,
                $reply->id,
                $type
            );

            $reaction = null;

            if ($added) {
                $reaction = $this->repository->findByUserAndReactable(
                    $actor->id,
                    Reply::class,
                    $reply->id
                );

                if ($reaction) {
                    event(new ReactionAdded($reaction));
                }
            }

            return ['added' => $added, 'reaction' => $reaction];
        });
    }

    public function delete(Reaction $reaction): bool
    {
        return $reaction->delete();
    }
}
