<?php

namespace Modules\Content\Listeners;

use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Models\User;
use Modules\Content\Events\ContentSubmitted;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyReviewersOnContentSubmitted
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    
    public function handle(ContentSubmitted $event): void
    {
        
        $reviewers = $this->getReviewers();

        if ($reviewers->isEmpty()) {
            return;
        }

        $contentTypeLabel = notifications_content_type_label($event->content);
        $contentTitle = $event->content->title ?? __('notifications.content.untitled');

        foreach ($reviewers as $reviewer) {
            $this->notificationService->notifyByPreferences(
                $reviewer,
                NotificationType::CourseUpdates->value,
                __('notifications.content.submitted_review_title', ['type' => $contentTypeLabel]),
                __('notifications.content.submitted_review_message', [
                    'author' => $event->user->name,
                    'type' => $contentTypeLabel,
                    'title' => $contentTitle,
                ]),
                [
                    'content_id' => $event->content->id,
                    'content_type' => get_class($event->content),
                    'author_id' => $event->user->id,
                ]
            );
        }
    }

    
    private function getReviewers()
    {
        return User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'reviewer', 'instructor']);
        })
            ->where('status', UserStatus::Active->value)
            ->get();
    }
}
