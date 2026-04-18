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

    /**
     * Handle the event.
     */
    public function handle(ContentSubmitted $event): void
    {
        // Get all users with reviewer/admin role
        $reviewers = $this->getReviewers();

        if ($reviewers->isEmpty()) {
            return;
        }

        $contentType = class_basename($event->content);
        $contentTitle = $event->content->title ?? 'Untitled';

        foreach ($reviewers as $reviewer) {
            $this->notificationService->notifyByPreferences(
                $reviewer,
                NotificationType::CourseUpdates->value,
                "{$contentType} menunggu review",
                "{$event->user->name} mengajukan {$contentType} \"{$contentTitle}\" untuk ditinjau.",
                [
                    'content_id' => $event->content->id,
                    'content_type' => get_class($event->content),
                    'author_id' => $event->user->id,
                ]
            );
        }
    }

    /**
     * Get users with reviewer permissions.
     */
    private function getReviewers()
    {
        return User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'reviewer', 'instructor']);
        })
            ->where('status', UserStatus::Active->value)
            ->get();
    }
}
