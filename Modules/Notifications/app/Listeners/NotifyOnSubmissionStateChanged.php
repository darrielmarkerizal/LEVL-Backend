<?php

declare(strict_types=1);

namespace Modules\Notifications\Listeners;

use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Events\SubmissionStateChanged;
use Modules\Notifications\Contracts\Services\GradingNotificationServiceInterface;

class NotifyOnSubmissionStateChanged
{
    public function __construct(
        private readonly GradingNotificationServiceInterface $notificationService
    ) {}

    public function handle(SubmissionStateChanged $event): void
    {
        $submission = $event->submission;
        $newState = $event->newState;

        if ($newState === SubmissionState::PendingManualGrading) {
            $this->notificationService->notifyManualGradingRequired($submission);
            return;
        }

        if ($newState === SubmissionState::Graded || $newState === SubmissionState::AutoGraded) {
            $this->notificationService->notifySubmissionGraded($submission);
            return;
        }
    }
}
