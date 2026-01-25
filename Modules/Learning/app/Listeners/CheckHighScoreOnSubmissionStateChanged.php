<?php

declare(strict_types=1);

namespace Modules\Learning\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Learning\Contracts\Services\SubmissionServiceInterface;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Events\SubmissionStateChanged;

class CheckHighScoreOnSubmissionStateChanged implements ShouldQueue
{
    public function __construct(
        private readonly SubmissionServiceInterface $submissionService
    ) {}

    public function handle(SubmissionStateChanged $event): void
    {
        if (in_array($event->newState, [
            SubmissionState::AutoGraded,
            SubmissionState::Graded,
            SubmissionState::Released
        ], true)) {
            $this->submissionService->checkAndDispatchNewHighScore($event->submission);
        }
    }
}
