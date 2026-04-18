<?php

namespace Modules\Forums\Listeners;

use Modules\Forums\Events\ThreadClosed;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyAuthorOnThreadClosed
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(ThreadClosed $event): void
    {
        $thread = $event->thread->loadMissing('author');
        if (! $thread->author || $thread->author->id === $event->actor->id) {
            return;
        }

        $this->notificationService->notifyByPreferences(
            $thread->author,
            NotificationType::Forum->value,
            'Thread ditutup',
            "{$event->actor->name} menutup thread \"{$thread->title}\".",
            [
                'thread_id' => $thread->id,
                'actor_id' => $event->actor->id,
            ]
        );
    }
}
