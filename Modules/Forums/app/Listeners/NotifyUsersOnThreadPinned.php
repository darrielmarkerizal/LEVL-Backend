<?php

namespace Modules\Forums\Listeners;

use Modules\Forums\Events\ThreadPinned;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyUsersOnThreadPinned
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(ThreadPinned $event): void
    {
        $thread = $event->thread;
        $scheme = $thread->scheme;

        $enrollments = \Modules\Enrollments\Models\Enrollment::where('course_id', $thread->scheme_id)
            ->where('user_id', '!=', $thread->author_id)
            ->with('user')
            ->get();

        foreach ($enrollments as $enrollment) {
            if (! $enrollment->user) {
                continue;
            }

            $this->notificationService->notifyByPreferences(
                $enrollment->user,
                NotificationType::Forum->value,
                "Important: {$thread->title}",
                "Thread dipin dan ditandai penting pada {$scheme->name}.",
                [
                    'thread_id' => $thread->id,
                    'thread_title' => $thread->title,
                    'scheme_name' => $scheme->name,
                ]
            );
        }
    }
}
