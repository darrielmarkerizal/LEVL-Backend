<?php

namespace Modules\Forums\Listeners;

use Modules\Forums\Events\ThreadResolved;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyAuthorOnThreadResolved
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(ThreadResolved $event): void
    {
        $thread = $event->thread->loadMissing('author');
        if (! $thread->author || $thread->author->id === $event->actor->id) {
            return;
        }

        $this->notificationService->notifyByPreferences(
            $thread->author,
            NotificationType::Forum->value,
            'Thread ditandai selesai',
            "{$event->actor->name} menandai thread \"{$thread->title}\" sebagai selesai.",
            [
                'thread_id' => $thread->id,
                'actor_id' => $event->actor->id,
            ]
        );
    }
}
