<?php

declare(strict_types=1);

namespace Modules\Notifications\Listeners;

use Modules\Grading\Events\GradesReleased;
use Modules\Notifications\Contracts\Services\GradingNotificationServiceInterface;

class NotifyOnGradesReleased
{
    public function __construct(
        private readonly GradingNotificationServiceInterface $notificationService
    ) {}

    public function handle(GradesReleased $event): void
    {
        $submissions = $event->submissions;

        if ($submissions->isEmpty()) {
            return;
        }

        $this->notificationService->notifyGradesReleased($submissions);
    }
}
