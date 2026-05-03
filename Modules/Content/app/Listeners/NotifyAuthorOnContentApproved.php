<?php

namespace Modules\Content\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Content\Events\ContentApproved;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyAuthorOnContentApproved implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    
    public function handle(ContentApproved $event): void
    {
        $author = $event->content->author;

        if (! $author) {
            return;
        }

        $contentTypeLabel = notifications_content_type_label($event->content);
        $contentTitle = $event->content->title ?? __('notifications.content.untitled');
        $approverName = $event->user->name;

        $this->notificationService->notifyByPreferences(
            $author,
            NotificationType::CourseUpdates->value,
            __('notifications.content.approved_title', ['type' => $contentTypeLabel]),
            __('notifications.content.approved_message', [
                'approver' => $approverName,
                'type' => $contentTypeLabel,
                'title' => $contentTitle,
            ]),
            [
                'content_id' => $event->content->id,
                'content_type' => get_class($event->content),
                'approver_id' => $event->user->id,
            ]
        );
    }
}
