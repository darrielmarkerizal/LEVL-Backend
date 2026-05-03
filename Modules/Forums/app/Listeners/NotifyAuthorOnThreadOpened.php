<?php

namespace Modules\Forums\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Forums\Events\ThreadOpened;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyAuthorOnThreadOpened implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(ThreadOpened $event): void
    {
        $thread = $event->thread->loadMissing('author');
        if (! $thread->author || $thread->author->id === $event->actor->id) {
            return;
        }

        $this->notificationService->notifyByPreferences(
            $thread->author,
            NotificationType::Forum->value,
            __('notifications.forum.thread_opened_title'),
            __('notifications.forum.thread_opened_message', [
                'actor' => $event->actor->name,
                'title' => $thread->title,
            ]),
            [
                'thread_id' => $thread->id,
                'actor_id' => $event->actor->id,
            ]
        );
    }
}
