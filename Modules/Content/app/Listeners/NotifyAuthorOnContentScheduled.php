<?php

namespace Modules\Content\Listeners;

use Modules\Content\Events\ContentScheduled;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyAuthorOnContentScheduled
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

        $this->notificationService->notifyByPreferences(
            $content->author,
            NotificationType::CourseUpdates->value,
            class_basename($content).' dijadwalkan',
            class_basename($content)." \"{$content->title}\" berhasil dijadwalkan untuk publikasi.",
            [
                'content_id' => $content->id,
                'content_type' => get_class($content),
                'scheduled_at' => $content->scheduled_at?->toIso8601String(),
            ]
        );
    }
}
