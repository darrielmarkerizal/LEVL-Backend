<?php

namespace Modules\Forums\Contracts\Services;

use Modules\Auth\Models\User;
use Modules\Forums\Models\Reply;
use Modules\Forums\Models\Thread;

interface ModerationServiceInterface
{
    public function pinThread(Thread $thread, User $moderator): Thread;

    public function unpinThread(Thread $thread, User $moderator): Thread;

    public function closeThread(Thread $thread, User $moderator): Thread;

    public function reopenThread(Thread $thread, User $moderator): Thread;

    public function markAsAcceptedAnswer(Reply $reply, User $user): Reply;

    public function unmarkAsAcceptedAnswer(Reply $reply, User $user): Reply;
}
