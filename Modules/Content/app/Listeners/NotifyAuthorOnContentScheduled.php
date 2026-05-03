<?php

namespace Modules\Content\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Content\Events\ContentScheduled;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyAuthorOnContentScheduled implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(ContentScheduled $event): void
    {
        $content = $event->content->loadMissing('author');
        if (! $content->author) {
            return;
        }

        $typeLabel = notifications_content_type_label($content);
        $title = $content->title ?? __('notifications.content.untitled');

        $this->notificationService->notifyByPreferences(
            $content->author,
            NotificationType::CourseUpdates->value,
            __('notifications.content.scheduled_title', ['type' => $typeLabel]),
            __('notifications.content.scheduled_message', [
                'type' => $typeLabel,
                'title' => $title,
            ]),
            [
                'content_id' => $content->id,
                'content_type' => get_class($content),
                'scheduled_at' => $content->scheduled_at?->toIso8601String(),
            ]
        );
    }
}
