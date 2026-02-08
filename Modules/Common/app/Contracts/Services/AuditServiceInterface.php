<?php

declare(strict_types=1);

namespace Modules\Common\Contracts\Services;

use Illuminate\Support\Collection;
use Modules\Common\Models\AuditLog;
use Modules\Grading\Models\Grade;
use Modules\Learning\Models\Question;
use Modules\Learning\Models\Submission;

interface AuditServiceInterface
{
    public function logSubmissionCreated(Submission $submission): void;

    public function logStateTransition(
        Submission $submission,
        string $oldState,
        string $newState,
        int $actorId
    ): void;

    public function logGrading(Grade $grade, int $instructorId): void;

    public function logAnswerKeyChange(
        Question $question,
        array $oldKey,
        array $newKey,
        int $instructorId
    ): void;

    public function logGradeOverride(
        Grade $grade,
        float $oldScore,
        float $newScore,
        string $reason,
        int $instructorId
    ): void;

    public function logOverrideGrant(
        int $assignmentId,
        int $studentId,
        string $overrideType,
        string $reason,
        int $instructorId
    ): void;

    public function search(array $filters): Collection;
}
