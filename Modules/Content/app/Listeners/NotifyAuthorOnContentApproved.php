<?php

namespace Modules\Content\Listeners;

use Modules\Content\Events\ContentApproved;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyAuthorOnContentApproved
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ContentApproved $event): void
    {
        $author = $event->content->author;

        if (! $author) {
            return;
        }

        $contentType = class_basename($event->content);
        $contentTitle = $event->content->title ?? 'Untitled';
        $approverName = $event->user->name;

        $this->notificationService->notifyByPreferences(
            $author,
            NotificationType::CourseUpdates->value,
            "{$contentType} disetujui",
            "{$approverName} menyetujui {$contentType} \"{$contentTitle}\".",
            [
                'content_id' => $event->content->id,
                'content_type' => get_class($event->content),
                'approver_id' => $event->user->id,
            ]
        );
    }
}
