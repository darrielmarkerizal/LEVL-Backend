<?php

namespace Modules\Content\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Content\Events\ContentPublished;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyAuthorOnContentPublished implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(ContentPublished $event): void
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
            __('notifications.content.published_title', ['type' => $typeLabel]),
            __('notifications.content.published_message', [
                'type' => $typeLabel,
                'title' => $title,
            ]),
            [
                'content_id' => $content->id,
                'content_type' => get_class($content),
                'published_at' => $content->published_at?->toIso8601String(),
            ]
        );
    }
}
