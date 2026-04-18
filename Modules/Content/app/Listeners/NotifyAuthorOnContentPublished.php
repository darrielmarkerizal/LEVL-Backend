<?php

namespace Modules\Content\Listeners;

use Modules\Content\Events\ContentPublished;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyAuthorOnContentPublished
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

        $this->notificationService->notifyByPreferences(
            $content->author,
            NotificationType::CourseUpdates->value,
            class_basename($content).' dipublikasikan',
            class_basename($content)." \"{$content->title}\" sudah dipublikasikan.",
            [
                'content_id' => $content->id,
                'content_type' => get_class($content),
                'published_at' => $content->published_at?->toIso8601String(),
            ]
        );
    }
}
