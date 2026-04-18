<?php

namespace Modules\Forums\Listeners;

use Modules\Forums\Events\ThreadCreated;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyInstructorOnThreadCreated
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(ThreadCreated $event): void
    {
        $thread = $event->thread;
        $scheme = $thread->scheme;

        if ($scheme && $scheme->instructor_id) {
            $scheme->loadMissing('instructor');
            if ($scheme->instructor) {
                $this->notificationService->notifyByPreferences(
                    $scheme->instructor,
                    NotificationType::Forum->value,
                    "New thread created: {$thread->title}",
                    "Ada thread baru pada {$scheme->name} oleh {$thread->author->name}.",
                    [
                        'thread_id' => $thread->id,
                        'thread_title' => $thread->title,
                        'author_name' => $thread->author->name,
                        'scheme_name' => $scheme->name,
                    ]
                );
            }
        }

        if ($thread->author->hasRole('instructor')) {
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
                    "Instructor posted: {$thread->title}",
                    "Instruktur {$thread->author->name} membuat thread baru pada {$scheme->name}.",
                    [
                        'thread_id' => $thread->id,
                        'thread_title' => $thread->title,
                        'instructor_name' => $thread->author->name,
                        'scheme_name' => $scheme->name,
                    ]
                );
            }
        }
    }
}
