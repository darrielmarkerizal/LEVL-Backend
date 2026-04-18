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

    /**
     * Handle the event.
     */
    public function handle(ContentRejected $event): void
    {
        $author = $event->content->author;

        if (! $author) {
            return;
        }

        $contentType = class_basename($event->content);
        $contentTitle = $event->content->title ?? 'Untitled';
        $reviewerName = $event->user->name;

        // Get rejection reason from workflow history
        $reason = ContentWorkflowHistory::where('content_type', get_class($event->content))
            ->where('content_id', $event->content->id)
            ->where('to_state', 'rejected')
            ->latest()
            ->value('note');

        $this->notificationService->notifyByPreferences(
            $author,
            NotificationType::CourseUpdates->value,
            "{$contentType} ditolak",
            "{$reviewerName} menolak {$contentType} \"{$contentTitle}\".".($reason ? " Alasan: {$reason}" : ''),
            [
                'content_id' => $event->content->id,
                'content_type' => get_class($event->content),
                'reviewer_id' => $event->user->id,
                'reason' => $reason,
            ]
        );
    }
}
