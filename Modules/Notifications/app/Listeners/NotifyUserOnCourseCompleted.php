<?php

declare(strict_types=1);

namespace Modules\Notifications\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;
use Modules\Schemes\Events\CourseCompleted;

class NotifyUserOnCourseCompleted implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public int $tries = 3;

    public int $maxExceptions = 2;

    public array $backoff = [5, 30, 120];

    public function __construct(private readonly NotificationService $notificationService) {}

    public function handle(CourseCompleted $event): void
    {
        $course = $event->course;
        $enrollment = $event->enrollment;

        if (! $course || ! $enrollment || ! $enrollment->user) {
            return;
        }

        $user = $enrollment->user;

        $this->notificationService->notifyByPreferences(
            $user,
            NotificationType::CourseCompleted->value,
            'Selamat! Course Selesai',
            sprintf(
                'Anda telah menyelesaikan course "%s". Selamat atas pencapaian Anda!',
                $course->title
            ),
            [
                'course_id' => $course->id,
                'course_slug' => $course->slug,
                'enrollment_id' => $enrollment->id,
                'completed_at' => $enrollment->completed_at?->toIso8601String(),
                'action_url' => '/courses/'.$course->slug,
            ],
            ['in_app']
        );
    }
}
