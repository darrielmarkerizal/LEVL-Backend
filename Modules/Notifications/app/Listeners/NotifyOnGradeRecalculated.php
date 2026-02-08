<?php

declare(strict_types=1);

namespace Modules\Notifications\Listeners;

use Modules\Grading\Events\GradeRecalculated;
use Modules\Notifications\Contracts\Services\GradingNotificationServiceInterface;

class NotifyOnGradeRecalculated
{
    public function __construct(
        private readonly GradingNotificationServiceInterface $notificationService
    ) {}

    public function handle(GradeRecalculated $event): void
    {
        $submission = $event->submission;
        $oldScore = $event->oldScore;
        $newScore = $event->newScore;

        $this->notificationService->notifyGradeRecalculated(
            $submission,
            $oldScore,
            $newScore
        );
    }
}
