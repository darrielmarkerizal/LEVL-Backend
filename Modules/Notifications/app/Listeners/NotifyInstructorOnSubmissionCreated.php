<?php

declare(strict_types=1);

namespace Modules\Notifications\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Auth\Models\User;
use Modules\Learning\Events\SubmissionCreated;
use Modules\Notifications\Contracts\Services\GradingNotificationServiceInterface;

class NotifyInstructorOnSubmissionCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public int $tries = 3;

    public int $maxExceptions = 2;

    public array $backoff = [5, 30, 120];

    public function __construct(
        private readonly GradingNotificationServiceInterface $notificationService
    ) {}

    public function handle(SubmissionCreated $event): void
    {
        $submission = $event->submission->fresh(['assignment.unit.course.instructors', 'assignment.unit.course.instructor', 'user']);

        if (! $submission || ! $submission->assignment) {
            return;
        }

        
        $this->notificationService->notifyManualGradingRequired($submission);
    }
}
