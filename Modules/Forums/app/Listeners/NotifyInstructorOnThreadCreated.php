<?php

namespace Modules\Forums\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Forums\Events\ThreadCreated;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyInstructorOnThreadCreated implements ShouldQueue
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
                    __('notifications.forum.new_thread_title', ['title' => $thread->title]),
                    __('notifications.forum.new_thread_message', [
                        'scheme' => $scheme->name,
                        'author' => $thread->author->name,
                    ]),
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
                    __('notifications.forum.instructor_posted_title', ['title' => $thread->title]),
                    __('notifications.forum.instructor_posted_message', [
                        'instructor' => $thread->author->name,
                        'scheme' => $scheme->name,
                    ]),
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
