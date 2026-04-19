<?php

namespace Modules\Content\Listeners;

use Modules\Content\Events\ContentRejected;
use Modules\Content\Models\ContentWorkflowHistory;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyAuthorOnContentRejected
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    
    public function handle(ContentRejected $event): void
    {
        $author = $event->content->author;

        if (! $author) {
            return;
        }

        $contentTypeLabel = notifications_content_type_label($event->content);
        $contentTitle = $event->content->title ?? __('notifications.content.untitled');
        $reviewerName = $event->user->name;

        
        $reason = ContentWorkflowHistory::where('content_type', get_class($event->content))
            ->where('content_id', $event->content->id)
            ->where('to_state', 'rejected')
            ->latest()
            ->value('note');

        $message = __('notifications.content.rejected_message', [
            'reviewer' => $reviewerName,
            'type' => $contentTypeLabel,
            'title' => $contentTitle,
        ]);
        if ($reason) {
            $message .= ' '.__('notifications.content.rejected_reason', ['reason' => $reason]);
        }

        $this->notificationService->notifyByPreferences(
            $author,
            NotificationType::CourseUpdates->value,
            __('notifications.content.rejected_title', ['type' => $contentTypeLabel]),
            $message,
            [
                'content_id' => $event->content->id,
                'content_type' => get_class($event->content),
                'reviewer_id' => $event->user->id,
                'reason' => $reason,
            ]
        );
    }
}
