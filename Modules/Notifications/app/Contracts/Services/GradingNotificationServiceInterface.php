<?php

declare(strict_types=1);

namespace Modules\Notifications\Contracts\Services;

use Illuminate\Support\Collection;
use Modules\Learning\Models\Submission;

interface GradingNotificationServiceInterface
{
    public function notifySubmissionGraded(Submission $submission): void;

    public function notifyGradesReleased(Collection $submissions): void;

    public function notifyManualGradingRequired(Submission $submission): void;

    public function notifyGradeRecalculated(
        Submission $submission,
        float $oldScore,
        float $newScore
    ): void;
}
