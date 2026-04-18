<?php

namespace Modules\Forums\Listeners;

use Modules\Forums\Events\ThreadUnresolved;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyAuthorOnThreadUnresolved
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(ThreadUnresolved $event): void
    {
        $thread = $event->thread->loadMissing('author');
        if (! $thread->author || $thread->author->id === $event->actor->id) {
            return;
        }

        $this->notificationService->notifyByPreferences(
            $thread->author,
            NotificationType::Forum->value,
            'Thread dibuka untuk diskusi',
            "{$event->actor->name} membuka kembali diskusi untuk thread \"{$thread->title}\".",
            [
                'thread_id' => $thread->id,
                'actor_id' => $event->actor->id,
            ]
        );
    }
}
