<?php

namespace Modules\Grading\Contracts\Services;

use Modules\Grading\Models\Grade;

interface GradingServiceInterface
{
    public function gradeSubmission(int $submissionId, array $data, int $gradedBy): Grade;

    public function updateGrade(Grade $grade, array $data, int $updatedBy): Grade;

    public function getGradeBySubmission(int $submissionId): ?Grade;
}
