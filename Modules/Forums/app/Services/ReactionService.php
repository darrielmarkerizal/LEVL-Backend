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

    public function addThread(User $actor, Thread $thread, string $type): Reaction
    {
        return DB::transaction(function () use ($actor, $thread, $type) {
            $reaction = Reaction::firstOrCreate([
                'user_id' => $actor->id,
                'reactable_type' => Thread::class,
                'reactable_id' => $thread->id,
                'type' => $type,
            ]);

            if ($reaction->wasRecentlyCreated) {
                event(new ReactionAdded($reaction));
            }

            return $reaction;
        });
    }

    public function addReply(User $actor, Reply $reply, string $type): Reaction
    {
        return DB::transaction(function () use ($actor, $reply, $type) {
            $reaction = Reaction::firstOrCreate([
                'user_id' => $actor->id,
                'reactable_type' => Reply::class,
                'reactable_id' => $reply->id,
                'type' => $type,
            ]);

            if ($reaction->wasRecentlyCreated) {
                event(new ReactionAdded($reaction));
            }

            return $reaction;
        });
    }

    public function delete(Reaction $reaction): bool
    {
        return $reaction->delete();
    }
}
